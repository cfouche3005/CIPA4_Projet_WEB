<?php
require_once ('database.php');
class Album
{
    // Récupère les infos de tous les albums
    public static function list_alb($conn) {
        try {
            if($conn){
                $sql = 'SELECT Album.album_id, album_name, album_date, album_image, album_type, Artist.artist_id, Artist.artist_pseudo 
                        FROM Album 
                        JOIN Compose ON Album.album_id = Compose.album_id 
                        JOIN Artist ON Artist.artist_id = Compose.artist_id';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($result as &$album) {
                    if (isset($album['album_image']) && $album['album_image']) {
                        $album['album_image'] = "https://music.cfouche.fr/" . $album['album_id'] . "/" . $album['album_image'];
                    }
                }
            }
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }

    // Récupère l'id de l'album à partir de son nom
    public static function id_alb($nom_album, $conn) {
        try {
            $sql = 'SELECT album_id FROM Album WHERE album_name = :nom_album';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom_album', $nom_album);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['album_id'];
    }

    // Récupère la date de l'album à partir de son id
    public static function date_alb($id_album, $conn) {
        try {
            $sql = 'SELECT album_date FROM Album WHERE album_id = :id_album';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_album', $id_album);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['album_date']; 
    }

    // Récupère l'image de l'album à partir de son id
    public static function image_alb($id_album, $conn) {
        try {
            $sql = 'SELECT album_image FROM Album WHERE album_id = :id_album';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_album', $id_album);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['album_image']) {
                return "https://music.cfouche.fr/" . $id_album . "/" . $result['album_image'];
            }
            return false;
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }

    // Récupère le type de l'album à partir de son id
    public static function type_alb($id_album, $conn) {
        try {
            $sql = 'SELECT album_type FROM Album WHERE album_id = :id_album';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_album', $id_album);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['album_type'];
    }

    //fonction qui a partir de l'id d'un album retourne toutes les infos et musiques de l'album
    public static function info_album($id_album, $conn){
        try {
            // Updated query for Album info + Artist info via Compose
            $sqlAlbum = 'SELECT Album.album_id, album_name, album_date, album_image, album_type, Artist.artist_id, Artist.artist_pseudo 
                         FROM Album 
                         JOIN Compose ON Album.album_id = Compose.album_id 
                         JOIN Artist ON Artist.artist_id = Compose.artist_id 
                         WHERE Album.album_id = :id_album';
            
            // Updated query for Music info via Contient
            $sqlMusic = 'SELECT MUSIC.music_id, music_title, music_duration, music_place 
                         FROM MUSIC 
                         JOIN Contient ON MUSIC.music_id = Contient.music_id 
                         WHERE Contient.album_id = :id_album 
                         ORDER BY music_place';
            
            // Updated query for Track Artists via Creer
            $sqlArtist = 'SELECT Artist.artist_id, artist_pseudo 
                          FROM Artist 
                          JOIN Creer ON Artist.artist_id = Creer.artist_id 
                          WHERE Creer.music_id = :id_music';

            $stmt = $conn->prepare($sqlAlbum);
            $stmt->bindParam(':id_album', $id_album);
            $stmt->execute();
            $resultAlbum = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process album image URL
            foreach ($resultAlbum as &$album) {
                if (isset($album['album_image']) && $album['album_image']) {
                    $album['album_image'] = "https://music.cfouche.fr/" . $album['album_id'] . "/" . $album['album_image'];
                }
            }
            unset($album); // Break reference

            $stmt1 = $conn->prepare($sqlMusic);
            $stmt1->bindParam(':id_album', $id_album);
            $stmt1->execute();
            $resultMusic = $stmt1->fetchAll(PDO::FETCH_ASSOC);

            $Endresult  = array();

            foreach ($resultMusic as $music) {
                $stmt2 = $conn->prepare($sqlArtist);
                $stmt2->bindParam(':id_music', $music['music_id']);
                $stmt2->execute();
                $resultArtist = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                
                $music['artists'] = $resultArtist;
                // Generate music link
                $music['lien_music'] = "https://music.cfouche.fr/" . $id_album . "/" . $music['music_id'] . ".opus";
                
                array_push($Endresult, $music);
            }

            $fresult['album'] = $resultAlbum;
            $fresult['musics'] = $Endresult;
            return $fresult;
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }

    // Récupère les albums d'un user
    public static function album_user($id_user, $conn) {
        // Feature not supported by current schema (no User-Album like table)
        return []; 
    }

    // Récupère 5 albums aléatoires 
    public static function album_random($numbers, $conn) {
        try {
            $sql = 'SELECT Album.album_id, album_name, album_image, Artist.artist_id, artist_pseudo 
                FROM Album 
                JOIN Compose ON Album.album_id = Compose.album_id 
                JOIN Artist ON Artist.artist_id = Compose.artist_id 
                ORDER BY RANDOM() LIMIT :numbers';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':numbers', $numbers);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($result as &$album) {
                if (isset($album['album_image']) && $album['album_image']) {
                    $album['album_image'] = "https://music.cfouche.fr/" . $album['album_id'] . "/" . $album['album_image'];
                }
            }
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }

    // Recherche des albums
    public static function search_alb($term, $conn) {
        try {
            $term = "%$term%";
            $sql = 'SELECT Album.album_id, album_name, album_image, album_type, Artist.artist_pseudo 
                    FROM Album 
                    JOIN Compose ON Album.album_id = Compose.album_id 
                    JOIN Artist ON Artist.artist_id = Compose.artist_id 
                    WHERE LOWER(album_name) LIKE LOWER(:term) OR LOWER(Artist.artist_pseudo) LIKE LOWER(:term)
                    LIMIT 10';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':term', $term);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($result as &$album) {
                if (isset($album['album_image']) && $album['album_image']) {
                    $album['album_image'] = "https://music.cfouche.fr/" . $album['album_id'] . "/" . $album['album_image'];
                }
            }
            return $result;
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }
}