<?php

class Artist
{
    // Récupère tous les artistes et leurs informations
    public static function all_art($conn) {
        try {
            if($conn){
                $sql = 'SELECT * FROM Artist';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }

    // Récupère l'id d'un artiste' à partir de son pseudo
    public static function id_art($pseudo_artist, $conn) {
        try {
            $sql = 'SELECT Artist_ID FROM Artist WHERE Artist_Pseudo = :pseudo_artist';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':pseudo_artist', $pseudo_artist);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Artist_ID'];
    }

    // Récupère le nom et info de l'artiste à partir de son id
    public static function name_info_art($id_artist, $conn) {
        try {
            $sql = 'SELECT Artist_Pseudo FROM Artist WHERE Artist_ID = :id_artist';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_artist', $id_artist);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Artist_Pseudo'];
    }

    // Récupère le lien de la biographie de l'artiste à partir de son id
    // Note: The new schema doesn't seem to have a biography link column, returning null or removing if not needed.
    // Assuming it might be added later or not present. For now, returning null to avoid errors if called.
    public static function biographie_lien_art($id_artist, $conn) {
        return null; 
    }

    // Récupère le type de l'artiste à partir de son id
    // Note: The new schema doesn't seem to have an artist type table.
    public static function type_art($id_artist, $conn) {
        return null;
    }

    // Récupère la photo de l'artiste à partir de son id
    public static function photo_art($id_artist, $conn) {
        try {
            $sql = 'SELECT Artist_Image FROM Artist WHERE Artist_ID = :id_artist';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_artist', $id_artist);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['Artist_Image']) {
                return "https://music.cfouche.fr/artists/" . $result['Artist_Image'];
            }
            return false;
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }

    //fonction qui récupère les informations de l'artiste, les albums dans lequel il est ainsi que 6 musiques produites par l'artiste
    public static function info_artiste($id_artist, $conn) {
        try {
            // Get Artist Info
            $sqlArtist = 'SELECT * FROM Artist WHERE Artist_ID = :id_artist';
            $stmt = $conn->prepare($sqlArtist);
            $stmt->bindParam(':id_artist', $id_artist);
            $stmt->execute();
            $artistInfo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$artistInfo) return false;

            // Get Albums composed by Artist
            $sqlAlbum = 'SELECT Album.* FROM Album JOIN Compose ON Album.Album_ID = Compose.Album_ID WHERE Compose.Artist_ID = :id_artist';
            $stmt = $conn->prepare($sqlAlbum);
            $stmt->bindParam(':id_artist', $id_artist);
            $stmt->execute();
            $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get Top 6 Musics created by Artist
            $sqlMusic = 'SELECT MUSIC.* FROM MUSIC JOIN Creer ON MUSIC.Music_ID = Creer.Music_ID WHERE Creer.Artist_ID = :id_artist LIMIT 6';
            $stmt = $conn->prepare($sqlMusic);
            $stmt->bindParam(':id_artist', $id_artist);
            $stmt->execute();
            $musics = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Construct response
            $response = [
                'artist' => $artistInfo,
                'albums' => $albums,
                'top_tracks' => $musics
            ];

            return $response;
           
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }
}