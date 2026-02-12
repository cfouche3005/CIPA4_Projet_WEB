<?php
require_once ('database.php');
class Album
{
    // Récupère les infos de tous les albums
    public static function list_alb($conn) {
        try {
            if($conn){
                $sql = 'SELECT Album.Album_ID, Album_Name, Album_Date, Album_Image, Album_Type, Artist.Artist_ID, Artist.Artist_Pseudo 
                        FROM Album 
                        JOIN Compose ON Album.Album_ID = Compose.Album_ID 
                        JOIN Artist ON Artist.Artist_ID = Compose.Artist_ID';
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($result as &$album) {
                    if ($album['Album_Image']) {
                        $album['Album_Image'] = "https://music.cfouche.fr/" . $album['Album_ID'] . "/" . $album['Album_Image'];
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
            $sql = 'SELECT Album_ID FROM Album WHERE Album_Name = :nom_album';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom_album', $nom_album);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Album_ID'];
    }

    // Récupère la date de l'album à partir de son id
    public static function date_alb($id_album, $conn) {
        try {
            $sql = 'SELECT Album_Date FROM Album WHERE Album_ID = :id_album';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_album', $id_album);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Album_Date']; 
    }

    // Récupère l'image de l'album à partir de son id
    public static function image_alb($id_album, $conn) {
        try {
            $sql = 'SELECT Album_Image FROM Album WHERE Album_ID = :id_album';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_album', $id_album);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && $result['Album_Image']) {
                return "https://music.cfouche.fr/" . $id_album . "/" . $result['Album_Image'];
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
            $sql = 'SELECT Album_Type FROM Album WHERE Album_ID = :id_album';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_album', $id_album);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Album_Type'];
    }

    //fonction qui a partir de l'id d'un album retourne toutes les infos et musiques de l'album
    public static function info_album($id_album, $conn){
        try {
            // Updated query for Album info + Artist info via Compose
            $sqlAlbum = 'SELECT Album.Album_ID, Album_Name, Album_Date, Album_Image, Album_Type, Artist.Artist_ID, Artist.Artist_Pseudo 
                         FROM Album 
                         JOIN Compose ON Album.Album_ID = Compose.Album_ID 
                         JOIN Artist ON Artist.Artist_ID = Compose.Artist_ID 
                         WHERE Album.Album_ID = :id_album';
            
            // Updated query for Music info via Contient
            $sqlMusic = 'SELECT MUSIC.Music_ID, Music_Title, Music_Duration, Music_Place 
                         FROM MUSIC 
                         JOIN Contient ON MUSIC.Music_ID = Contient.Music_ID 
                         WHERE Contient.Album_ID = :id_album 
                         ORDER BY Music_Place';
            
            // Updated query for Track Artists via Creer
            $sqlArtist = 'SELECT Artist.Artist_ID, Artist_Pseudo 
                          FROM Artist 
                          JOIN Creer ON Artist.Artist_ID = Creer.Artist_ID 
                          WHERE Creer.Music_ID = :id_music';

            $stmt = $conn->prepare($sqlAlbum);
            $stmt->bindParam(':id_album', $id_album);
            $stmt->execute();
            $resultAlbum = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Process album image URL
            foreach ($resultAlbum as &$album) {
                if ($album['Album_Image']) {
                    $album['Album_Image'] = "https://music.cfouche.fr/" . $album['Album_ID'] . "/" . $album['Album_Image'];
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
                $stmt2->bindParam(':id_music', $music['Music_ID']);
                $stmt2->execute();
                $resultArtist = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                
                $music['artists'] = $resultArtist;
                // Generate music link
                $music['lien_music'] = "https://music.cfouche.fr/" . $id_album . "/" . $music['Music_ID'] . ".opus";
                
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
            $sql = 'SELECT Album.Album_ID, Album_Name, Album_Image, Artist.Artist_ID, Artist_Pseudo 
                FROM Album 
                JOIN Compose ON Album.Album_ID = Compose.Album_ID 
                JOIN Artist ON Artist.Artist_ID = Compose.Artist_ID 
                ORDER BY RANDOM() LIMIT :numbers';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':numbers', $numbers);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($result as &$album) {
                if ($album['Album_Image']) {
                    $album['Album_Image'] = "https://music.cfouche.fr/" . $album['Album_ID'] . "/" . $album['Album_Image'];
                }
            }
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }
}