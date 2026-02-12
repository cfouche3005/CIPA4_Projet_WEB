<?php

class Playlist
{
    // Récupère les informations de toutes les playlists
    public static function info_pla($conn) {
        try {
            if($conn){
                $sql = 'SELECT * FROM Playlist';
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

    // Récupère le nom de la playlist à partir de son id
    public static function name_pla($id_playlist, $conn) {
        try {
            $sql = 'SELECT playlist_name FROM Playlist WHERE playlist_id = :id_playlist';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_playlist', $id_playlist);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['playlist_name'];
    }
    //Récupère l'id de la playlist depuis son nom : 
    public static function id_pla($nom_playlist, $conn) {
        try {
            $sql = 'SELECT playlist_id FROM Playlist WHERE playlist_name = :nom_playlist';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom_playlist', $nom_playlist);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['playlist_id'];
    }

    // Récupère la date de modification de la playlist à partir de son id
    // Note: The new schema doesn't have a modification date column in Playlist table.
    // Returning null or creation date as fallback if needed, or removing.
    // Assuming we might use creation date or just return null.
    public static function date_modif_pla($id_playlist, $conn) {
        return null; 
    }

    // Récupère la date de creation de la playlist à partir de son id
    public static function date_creation_pla($id_playlist, $conn) {
        try {
            $sql = 'SELECT playlist_creation_date FROM Playlist WHERE playlist_id = :id_playlist';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_playlist', $id_playlist);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['playlist_creation_date'];
    }

    // Fonction qui crée une playlist 
    public static function creer_playlist($nom_playlist, $id_user, $conn) {
        try {
            //vérification si la playlist existe déjà :
            $sqlVerif = 'SELECT COUNT(*) as count FROM Playlist WHERE playlist_name = :nom_playlist AND user_id = :id_user';
            $stmtVerif = $conn->prepare($sqlVerif);
            $stmtVerif->bindParam(':nom_playlist', $nom_playlist);
            $stmtVerif->bindParam(':id_user', $id_user);
            $stmtVerif->execute();
            $resultVerif = $stmtVerif->fetch(PDO::FETCH_ASSOC);
            
            if($resultVerif['count']==0){
                if (strtolower($nom_playlist) == "favoris"){
                    return "playlist-exist";
                }
                else{
                    // Generate a UUID for Playlist_ID (since it's VARCHAR(50) and likely not auto-increment)
                    // Or let the DB handle it if there's a trigger/default. 
                    // Assuming we need to generate it in PHP:
                    $playlist_id = uniqid(); 

                    //si elle n'existe pas, on l'ajoute :
                    $sql = 'INSERT INTO Playlist (playlist_id, playlist_name, playlist_creation_date, user_id) VALUES (:playlist_id, :nom_playlist, CURRENT_DATE, :id_user)';
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':playlist_id', $playlist_id);
                    $stmt->bindParam(':nom_playlist', $nom_playlist);
                    $stmt->bindParam(':id_user', $id_user);
                    $stmt->execute();
                    return true;
                }
            }
            else {
                return "playlist-exist";
            }
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }

    // Fonction qui permet de modifier une playlist (son nom)
    public static function modifier_playlist($nom_playlist, $id_playlist, $conn) {
        try {
            $sql = 'UPDATE Playlist SET playlist_name = :nom_playlist WHERE playlist_id = :id_playlist';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom_playlist', $nom_playlist);
            $stmt->bindParam(':id_playlist', $id_playlist);
            $stmt->execute();
            return true;
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }

    // Fonction qui permet de supprimer une playlist
    public static function delete_playlist($id_playlist, $conn)
    {
        try
        {
            // First delete entries in Appartient (Music in Playlist)
            $reqDelMusic = 'DELETE FROM Appartient WHERE playlist_id=:id_playlist';
            $stmtDelMusic = $conn->prepare($reqDelMusic);
            $stmtDelMusic->bindParam(':id_playlist', $id_playlist);
            $stmtDelMusic->execute();

            $request = 'DELETE FROM Playlist WHERE playlist_id=:id_playlist';
            $statement = $conn->prepare($request);
            $statement->bindParam(':id_playlist', $id_playlist);
            $statement->execute();
        }
        catch (PDOException $exception)
        {
          error_log('Request error: '.$exception->getMessage());
          return false;
        }
        return true;
      }

    //fonction qui ajoute une musique à une playlist :
    public static function add_music_playlist($id_playlist, $id_music, $conn)
    {
        try
        {
            // Vérifier si la combinaison id_music/id_playlist existe déjà
            $checkSql = 'SELECT COUNT(*) FROM Appartient WHERE playlist_id = :id_playlist AND music_id = :id_music';
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(':id_playlist', $id_playlist);
            $checkStmt->bindParam(':id_music', $id_music);
            $checkStmt->execute();
            $count = $checkStmt->fetchColumn();

            if ($count > 0) {
                return false;
            }

            // Effectuer l'insertion
            $sql = 'INSERT INTO Appartient (playlist_id, music_id) VALUES (:id_playlist, :id_music)';
            $statement = $conn->prepare($sql);
            $statement->bindParam(':id_playlist', $id_playlist);
            $statement->bindParam(':id_music', $id_music);
            $statement->execute();

            return true;
        }
        catch (PDOException $exception)
        {
            return false; 
        }
    }

    //fonction qui supprime une musique d'une playlist :
    public static function delete_music_playlist($id_playlist, $id_music, $conn) 
        {
            try
            {
                $request = 'DELETE FROM Appartient WHERE playlist_id=:id_playlist AND music_id=:id_music';
                $statement = $conn->prepare($request);
                $statement->bindParam(':id_playlist', $id_playlist);
                $statement->bindParam(':id_music', $id_music);
                $statement->execute();
                return true;
                }
                catch (PDOException $exception)
                {
                error_log('Request error: '.$exception->getMessage());
                return false;
                }
            }       

    //fonction qui récupère les musiques d'une playlist à partir de son id
    public static function get_music_playlist($id_playlist, $conn) 
        {
            try
            {   
                // Get Playlist Info
                $sqlPlaylist = 'SELECT playlist_id, playlist_name, playlist_creation_date FROM Playlist WHERE playlist_id=:id_playlist';
                $stmt = $conn->prepare($sqlPlaylist);
                $stmt->bindParam(':id_playlist', $id_playlist);
                $stmt->execute();
                $resultPlaylist = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
                // Get Musics in Playlist
                $sqlMusic = 'SELECT m.music_id, m.music_title, m.music_duration 
                             FROM MUSIC m 
                             JOIN Appartient a ON m.music_id = a.music_id 
                             WHERE a.playlist_id = :id_playlist';
                $stmt1 = $conn->prepare($sqlMusic);
                $stmt1->bindParam(':id_playlist', $id_playlist);
                $stmt1->execute();
                $resultMusic = $stmt1->fetchAll(PDO::FETCH_ASSOC);

                // Get Artists for each Music
                $sqlArtist = 'SELECT Artist.artist_id, artist_pseudo 
                              FROM Artist 
                              JOIN Creer ON Artist.artist_id = Creer.artist_id 
                              WHERE Creer.music_id = :id_music';

                $Endresult  = array();
                foreach ($resultMusic as $music ) {
                    $stmt2 = $conn->prepare($sqlArtist);
                    $stmt2->bindParam(':id_music', $music['music_id']);
                    $stmt2->execute();
                    $resultArtist = $stmt2->fetchAll(PDO::FETCH_ASSOC);
                    
                    $music['artists'] = $resultArtist;
                    
                    // Generate music link (Need Album ID for this)
                    // We need to fetch Album ID for the music to generate the link
                    $sqlAlbum = 'SELECT album_id FROM Contient WHERE music_id = :id_music LIMIT 1';
                    $stmtAlb = $conn->prepare($sqlAlbum);
                    $stmtAlb->bindParam(':id_music', $music['music_id']);
                    $stmtAlb->execute();
                    $albRes = $stmtAlb->fetch(PDO::FETCH_ASSOC);
                    
                    if ($albRes) {
                         $music['lien_music'] = "https://music.cfouche.fr/" . $albRes['album_id'] . "/" . $music['music_id'] . ".opus";
                    } else {
                        $music['lien_music'] = null;
                    }

                    array_push($Endresult,$music);
                }
                
                $fresult['playlist']=$resultPlaylist;
                $fresult['musics']=$Endresult;
                return $fresult;
            }
            catch (PDOException $exception)
            {
                error_log('Request error: '.$exception->getMessage());
                return false;
            }
        }

    //Récupère les playlists d'un utilisateur à partir de son id
    public static function playlist_user($id_user, $conn) { 
        try {
            $sql = 'SELECT playlist_id, playlist_name, playlist_creation_date FROM Playlist WHERE user_id = :id_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }
}