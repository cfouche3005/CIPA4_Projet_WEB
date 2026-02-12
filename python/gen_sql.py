import os
import sys
import mutagen
import musicbrainzngs
import logging
import subprocess
import urllib.request
import urllib.parse
import json

# Configure logging
logging.basicConfig(level=logging.INFO, format='-- %(levelname)s: %(message)s', stream=sys.stderr)

# Suppress musicbrainzngs internal logging
logging.getLogger("musicbrainzngs").setLevel(logging.WARNING)

# Configure musicbrainzngs
musicbrainzngs.set_useragent("CIPA4_GenSQL", "0.1", "contact@example.com")

# Configuration
OUTPUT_DIR = "processed_music"

# Cache to avoid repeated API calls
album_art_cache = {}
artist_art_cache = {}

# Data collection structures
sql_data = {
    "artists": {}, # id -> {name, image}
    "albums": {},  # id -> {name, date, image, type}
    "musics": {},  # id -> {title, duration, place}
    "genres": {},  # id -> name
    "compose": set(), # (album_id, artist_id)
    "creer": set(),   # (music_id, artist_id)
    "contient": set(), # (music_id, album_id)
    "possede": set(),  # (genre_id, music_id)
}

# User-Agent to avoid 403 Forbidden on Wikimedia
USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'

def escape_sql(value):
    if value is None:
        return "NULL"
    return "'" + str(value).replace("'", "''") + "'"

def download_image(url, dest_path):
    try:
        # Encode URL properly if it contains spaces or other chars, but preserve existing encoding
        # A simple way is to rely on urllib handling, but sometimes we need to be careful.
        # For now, we assume the URL is valid or mostly valid.

        req = urllib.request.Request(url, headers={'User-Agent': USER_AGENT})
        with urllib.request.urlopen(req) as response:
            content_type = response.getheader('Content-Type')
            if 'image' not in content_type and 'application/octet-stream' not in content_type:
                logging.warning(f"URL {url} returned {content_type}, not an image. Skipping.")
                return False

            with open(dest_path, 'wb') as f:
                f.write(response.read())
        return True
    except Exception as e:
        logging.error(f"Failed to download {url}: {e}")
        return False

def resolve_wikimedia_image(wiki_url):
    """
    Resolves a Wikimedia Commons page URL to the actual image URL using the MediaWiki API.
    """
    try:
        # Extract filename from URL
        # Expected format: https://commons.wikimedia.org/wiki/File:Name.jpg
        if 'File:' not in wiki_url:
            return None

        filename = wiki_url.split('File:')[-1]
        # Decode URL encoding (e.g. %20 -> space)
        filename = urllib.parse.unquote(filename)

        api_url = "https://commons.wikimedia.org/w/api.php"
        params = {
            "action": "query",
            "titles": f"File:{filename}",
            "prop": "imageinfo",
            "iiprop": "url",
            "format": "json"
        }

        query_string = urllib.parse.urlencode(params)
        full_url = f"{api_url}?{query_string}"

        req = urllib.request.Request(full_url, headers={'User-Agent': USER_AGENT})
        with urllib.request.urlopen(req) as response:
            data = json.loads(response.read())

        pages = data.get('query', {}).get('pages', {})
        for page_id, page_data in pages.items():
            if 'imageinfo' in page_data:
                return page_data['imageinfo'][0]['url']

    except Exception as e:
        logging.warning(f"Failed to resolve Wikimedia URL {wiki_url}: {e}")

    return None

def extract_art_from_tags(audio, dest_path):
    try:
        # FLAC / Vorbis
        if hasattr(audio, 'pictures') and audio.pictures:
            with open(dest_path, 'wb') as f:
                f.write(audio.pictures[0].data)
            return True

        # ID3 (MP3)
        if audio.tags:
            for key in audio.tags.keys():
                if key.startswith('APIC'):
                    with open(dest_path, 'wb') as f:
                        f.write(audio.tags[key].data)
                    return True
            # MP4 / M4A
            if 'covr' in audio.tags:
                 with open(dest_path, 'wb') as f:
                    f.write(audio.tags['covr'][0])
                 return True
    except Exception as e:
        logging.warning(f"Failed to extract art from tags: {e}")
    return False

def get_album_art(audio, release_id):
    if not release_id:
        return None

    filename = f"{release_id}.jpg"
    dest_dir = os.path.join(OUTPUT_DIR, release_id)
    dest_path = os.path.join(dest_dir, filename)

    # Ensure directory exists
    os.makedirs(dest_dir, exist_ok=True)

    if release_id in album_art_cache:
        return album_art_cache[release_id]

    # Check if already exists
    if os.path.exists(dest_path):
        album_art_cache[release_id] = filename
        return filename

    # 1. Try to extract from tags
    if extract_art_from_tags(audio, dest_path):
        logging.info(f"Extracted cover art for {release_id}")
        album_art_cache[release_id] = filename
        return filename

    # 2. Fetch from MusicBrainz
    try:
        logging.info(f"Fetching cover art URL for release {release_id}")
        data = musicbrainzngs.get_image_list(release_id)
        for img in data['images']:
            if img.get('front', False):
                image_url = img['image']
                if download_image(image_url, dest_path):
                    album_art_cache[release_id] = filename
                    return filename
    except Exception as e:
        logging.warning(f"Could not fetch cover art for {release_id}: {e}")

    album_art_cache[release_id] = None
    return None

def get_artist_art(artist_id):
    if not artist_id:
        return None

    filename = f"{artist_id}.jpg"
    dest_dir = os.path.join(OUTPUT_DIR, "artists")
    dest_path = os.path.join(dest_dir, filename)

    os.makedirs(dest_dir, exist_ok=True)

    if artist_id in artist_art_cache:
        return artist_art_cache[artist_id]

    if os.path.exists(dest_path):
        artist_art_cache[artist_id] = filename
        return filename

    try:
        logging.info(f"Fetching artist info for {artist_id}")
        # Fetch artist with URL relations
        artist_data = musicbrainzngs.get_artist_by_id(artist_id, includes=['url-rels'])
        artist_info = artist_data.get('artist', {})

        image_url = None
        for rel in artist_info.get('url-relation-list', []):
            if rel.get('type') == 'image':
                target = rel.get('target', '')

                # Handle Wikimedia Commons pages
                if 'commons.wikimedia.org/wiki/File:' in target:
                    logging.info(f"Resolving Wikimedia URL: {target}")
                    resolved_url = resolve_wikimedia_image(target)
                    if resolved_url:
                        target = resolved_url
                    else:
                        continue # Skip if resolution failed

                # Basic check to ensure it's likely an image file
                if target.lower().endswith(('.jpg', '.jpeg', '.png', '.gif', '.webp')) or 'upload.wikimedia.org' in target:
                    image_url = target
                    break

        if image_url:
            if download_image(image_url, dest_path):
                artist_art_cache[artist_id] = filename
                return filename
        else:
            logging.info(f"No suitable image relation found for artist {artist_id}")

    except Exception as e:
        logging.warning(f"Could not fetch artist art for {artist_id}: {e}")

    artist_art_cache[artist_id] = None
    return None

def convert_to_opus(input_path, output_path):
    try:
        # Ensure output directory exists
        os.makedirs(os.path.dirname(output_path), exist_ok=True)

        # Skip if already exists (optional, but saves time)
        # if os.path.exists(output_path):
        #     logging.info(f"File already exists: {output_path}")
        #     return

        cmd = [
            "ffmpeg",
            "-i", input_path,
            "-c:a", "libopus",
            "-b:a", "128k",
            "-vn", # No video (cover art)
            "-y",  # Overwrite
            "-loglevel", "error",
            output_path
        ]

        logging.info(f"Converting {input_path} -> {output_path}")
        subprocess.run(cmd, check=True)

    except subprocess.CalledProcessError as e:
        logging.error(f"FFmpeg failed for {input_path}: {e}")
    except FileNotFoundError:
        logging.error("FFmpeg not found. Please install ffmpeg to convert music.")
    except Exception as e:
        logging.error(f"Conversion error for {input_path}: {e}")

def process_file(file_path):
    logging.info(f"Processing {file_path}")
    try:
        audio = mutagen.File(file_path)
        if not audio:
            logging.warning(f"No audio data found in {file_path}")
            return
    except Exception as e:
        logging.error(f"Error reading {file_path}: {e}")
        return

    tags = audio.tags
    if not tags:
        logging.warning(f"No tags found in {file_path}")
        return

    # Helper to get list of values
    def get_list(key):
        return tags.get(key, [])

    # Helper to get first value
    def get_first(key, default=None):
        l = get_list(key)
        return l[0] if l else default

    # --- Music Info ---
    music_id = get_first('musicbrainz_trackid')
    if not music_id:
        logging.warning(f"No track ID for {file_path}, skipping")
        return

    music_title = get_first('title', 'Unknown Title')
    music_duration = int(audio.info.length) if audio.info else 0
    music_place = get_first('tracknumber')
    if music_place and '/' in music_place:
        music_place = music_place.split('/')[0]
    try:
        music_place = int(music_place)
    except:
        music_place = 0

    sql_data["musics"][music_id] = {
        "title": music_title,
        "duration": music_duration,
        "place": music_place
    }

    # --- Album Info ---
    album_id = get_first('musicbrainz_albumid')
    if album_id:
        album_name = get_first('album', 'Unknown Album')
        album_date = get_first('date', '')
        album_type = get_first('musicbrainz_albumtype', 'album')

        # Extract/Fetch Album Art
        album_image = get_album_art(audio, album_id)

        sql_data["albums"][album_id] = {
            "name": album_name,
            "date": album_date,
            "type": album_type,
            "image": album_image
        }

        # Contient (Music -> Album)
        sql_data["contient"].add((music_id, album_id))

        # --- Conversion ---
        # Convert to Opus and save to processed_music/album_id/music_id.opus
        output_path = os.path.join(OUTPUT_DIR, album_id, f"{music_id}.opus")
        convert_to_opus(file_path, output_path)

    # --- Artists (Track Artists) ---
    # User requested to use 'artists_credit' for track credits
    artist_ids = get_list('musicbrainz_artistid')
    artist_names = get_list('artists_credit')

    # Fallback if artists_credit is missing
    if not artist_names:
        artist_names = get_list('artist')

    if artist_ids:
        for i, aid in enumerate(artist_ids):
            # Try to match name with ID
            aname = artist_names[i] if i < len(artist_names) else "Unknown Artist"

            if aid not in sql_data["artists"]:
                # Fetch Artist Art
                aimage = get_artist_art(aid)

                sql_data["artists"][aid] = {
                    "name": aname,
                    "image": aimage
                }

            # Creer (Music -> Artist)
            sql_data["creer"].add((music_id, aid))

    # --- Album Artist (Compose) ---
    album_artist_ids = get_list('musicbrainz_albumartistid')
    # If no album artist ID, fallback to track artist ID
    if not album_artist_ids and artist_ids:
        album_artist_ids = artist_ids

    if album_id and album_artist_ids:
        for aid in album_artist_ids:
            if aid not in sql_data["artists"]:
                # Try to find name from album artist tags
                aa_names = get_list('album_artist') # or albumartists_credit
                name = aa_names[0] if aa_names else "Unknown Artist"

                # Fetch Artist Art
                aimage = get_artist_art(aid)

                sql_data["artists"][aid] = {
                    "name": name,
                    "image": aimage
                }

            sql_data["compose"].add((album_id, aid))

    # --- Genres ---
    raw_genres = get_list('genre')
    for g in raw_genres:
        sub_genres = [x.strip() for x in g.split(',')]
        for genre in sub_genres:
            if not genre: continue
            genre_id = genre.lower().replace(" ", "_")[:50]
            sql_data["genres"][genre_id] = genre

            # Possede (Genre -> Music)
            sql_data["possede"].add((genre_id, music_id))

def generate_sql():
    print("-- Generated SQL")

    print("\n-- Artists")
    for aid, data in sql_data["artists"].items():
        print(f"INSERT INTO Artist (Artist_ID, Artist_Pseudo, Artist_Image) VALUES ({escape_sql(aid)}, {escape_sql(data['name'])}, {escape_sql(data['image'])}) ON CONFLICT (Artist_ID) DO NOTHING;")

    print("\n-- Albums")
    for aid, data in sql_data["albums"].items():
        print(f"INSERT INTO Album (Album_ID, Album_Name, Album_Date, Album_Image, Album_Type) VALUES ({escape_sql(aid)}, {escape_sql(data['name'])}, {escape_sql(data['date'])}, {escape_sql(data['image'])}, {escape_sql(data['type'])}) ON CONFLICT (Album_ID) DO NOTHING;")

    print("\n-- Music")
    for mid, data in sql_data["musics"].items():
        print(f"INSERT INTO MUSIC (Music_ID, Music_Title, Music_Duration, Music_Place) VALUES ({escape_sql(mid)}, {escape_sql(data['title'])}, {data['duration']}, {data['place']}) ON CONFLICT (Music_ID) DO NOTHING;")

    print("\n-- Genres")
    for gid, name in sql_data["genres"].items():
        print(f"INSERT INTO GENRE (Genre_ID, Genre_Name) VALUES ({escape_sql(gid)}, {escape_sql(name)}) ON CONFLICT (Genre_ID) DO NOTHING;")

    print("\n-- Compose (Album -> Artist)")
    for alb, art in sql_data["compose"]:
        print(f"INSERT INTO Compose (Album_ID, Artist_ID) VALUES ({escape_sql(alb)}, {escape_sql(art)}) ON CONFLICT (Album_ID, Artist_ID) DO NOTHING;")

    print("\n-- Creer (Music -> Artist)")
    for mus, art in sql_data["creer"]:
        print(f"INSERT INTO Creer (Music_ID, Artist_ID) VALUES ({escape_sql(mus)}, {escape_sql(art)}) ON CONFLICT (Music_ID, Artist_ID) DO NOTHING;")

    print("\n-- Contient (Music -> Album)")
    for mus, alb in sql_data["contient"]:
        print(f"INSERT INTO Contient (Music_ID, Album_ID) VALUES ({escape_sql(mus)}, {escape_sql(alb)}) ON CONFLICT (Music_ID, Album_ID) DO NOTHING;")

    print("\n-- Possede (Genre -> Music)")
    for gen, mus in sql_data["possede"]:
        print(f"INSERT INTO Possede (Genre_ID, Music_ID) VALUES ({escape_sql(gen)}, {escape_sql(mus)}) ON CONFLICT (Genre_ID, Music_ID) DO NOTHING;")

if __name__ == "__main__":
    if len(sys.argv) > 1:
        for arg in sys.argv[1:]:
            if os.path.isdir(arg):
                for root, dirs, files in os.walk(arg):
                    for f in files:
                        if f.lower().endswith(('.flac', '.mp3', '.ogg', '.m4a')):
                            process_file(os.path.join(root, f))
            else:
                process_file(arg)

        # Generate SQL after processing all files
        generate_sql()
    else:
        print("-- Usage: python gen_sql.py <file_or_directory>")
