<?php

class Music
{
    // Récupère les infos de toutes les musiques
    public static function info_mus($conn) {
        try {
            if($conn){
                $sql = 'SELECT * FROM MUSIC';
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

    // Récupère l'id d'une musique à partir de son titre
    public static function id_mus($title_music, $conn) {
        try {
            $sql = 'SELECT music_id FROM MUSIC WHERE music_title = :title_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title_music', $title_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['music_id'];
    }

    // Récupère le titre de la musique à partir de son id
    public static function title_mus($id_music, $conn) {
        try {
            $sql = 'SELECT music_title FROM MUSIC WHERE music_id = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['music_title'];
    }

    // Récupère le lien de la musique à partir de son id
    public static function link_mus($id_music, $conn) {
        try {
            $sql = 'SELECT album_id, music_id FROM Contient WHERE music_id = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return "https://music.cfouche.fr/" . $result['album_id'] . "/" . $result['music_id'] . ".opus";
            }
            return false;
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }

    // Récupère le temps de la musique à partir de son id
    public static function time_mus($id_music, $conn) {
        try {
            $sql = 'SELECT music_duration FROM MUSIC WHERE music_id = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['music_duration'];
    }

    // Récupère la place de la musique dans l'album à partir de son id
    public static function place_album_mus($id_music, $conn) {
        try {
            $sql = 'SELECT music_place FROM MUSIC WHERE music_id = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['music_place'];
    }

    // Récupère le genre de la musique à partir de son id
    public static function genre_mus($id_music, $conn) {
        try {
            $sql = 'SELECT genre_name FROM GENRE JOIN Possede ON GENRE.genre_id = Possede.genre_id WHERE Possede.music_id = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['genre_name'];
    }

    public static function rechercherMusique($conn, $recherche) {
        try {
            $sql = 'SELECT * FROM MUSIC WHERE music_title LIKE :recherche';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':recherche', $recherche);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }

    //fonction qui permet de rechercher une musique à partir de son genre
    public static function rechercherMusiqueGenre($conn, $recherche) {
        try {
            $sql = 'SELECT * FROM MUSIC WHERE music_id IN (SELECT music_id FROM Possede JOIN GENRE ON Possede.genre_id = GENRE.genre_id WHERE genre_name LIKE :recherche)';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':recherche', $recherche);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }

    //fonction qui permet de rechercher une musique à partir de son artiste
    public static function rechercherMusiqueArtiste($conn, $recherche) {
        try {
            $sql = 'SELECT * FROM MUSIC WHERE music_id IN (SELECT music_id FROM Creer JOIN Artist ON Creer.artist_id = Artist.artist_id WHERE artist_pseudo LIKE :recherche)';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':recherche', $recherche);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }

    //vérifie si une musique est dans les favoris d'un utilisateur spécifique
    public static function verif_music_like($id_music, $id_user, $conn){
        try {
            $sql = "SELECT COUNT(*) as count FROM Aime WHERE music_id = :id_music AND user_id = :id_user";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if($result['count'] == 1){
                return true;
            }
            else return "not found";
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }

    //ajoute une musique dans les favoris d'un user spécifique
    public static function ajout_music_like($id_music, $id_user, $conn){
        try {
            $sql = "INSERT INTO Aime (music_id, user_id) VALUES (:id_music, :id_user)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            return true;
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }

    //supprime une musique dans les favoris d'un user spécifique
    public static function delete_music_like($id_music, $id_user, $conn){
        try {
            $sql = "DELETE FROM Aime WHERE music_id = :id_music AND user_id = :id_user";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            return true;
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }
}