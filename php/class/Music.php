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
            $sql = 'SELECT Music_ID FROM MUSIC WHERE Music_Title = :title_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':title_music', $title_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Music_ID'];
    }

    // Récupère le titre de la musique à partir de son id
    public static function title_mus($id_music, $conn) {
        try {
            $sql = 'SELECT Music_Title FROM MUSIC WHERE Music_ID = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Music_Title'];
    }

    // Récupère le lien de la musique à partir de son id
    public static function link_mus($id_music, $conn) {
        try {
            $sql = 'SELECT Album_ID, Music_ID FROM Contient WHERE Music_ID = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return "https://music.cfouche.fr/" . $result['Album_ID'] . "/" . $result['Music_ID'] . ".opus";
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
            $sql = 'SELECT Music_Duration FROM MUSIC WHERE Music_ID = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Music_Duration'];
    }

    // Récupère la place de la musique dans l'album à partir de son id
    public static function place_album_mus($id_music, $conn) {
        try {
            $sql = 'SELECT Music_Place FROM MUSIC WHERE Music_ID = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Music_Place'];
    }

    // Récupère le genre de la musique à partir de son id
    public static function genre_mus($id_music, $conn) {
        try {
            $sql = 'SELECT Genre_Name FROM GENRE JOIN Possede ON GENRE.Genre_ID = Possede.Genre_ID WHERE Possede.Music_ID = :id_music';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_music', $id_music);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['Genre_Name'];
    }

    public static function rechercherMusique($conn, $recherche) {
        try {
            $sql = 'SELECT * FROM MUSIC WHERE Music_Title LIKE :recherche';
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
            $sql = 'SELECT * FROM MUSIC WHERE Music_ID IN (SELECT Music_ID FROM Possede JOIN GENRE ON Possede.Genre_ID = GENRE.Genre_ID WHERE Genre_Name LIKE :recherche)';
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
            $sql = 'SELECT * FROM MUSIC WHERE Music_ID IN (SELECT Music_ID FROM Creer JOIN Artist ON Creer.Artist_ID = Artist.Artist_ID WHERE Artist_Pseudo LIKE :recherche)';
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
            $sql = "SELECT COUNT(*) as count FROM Aime WHERE Music_ID = :id_music AND User_ID = :id_user";
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
            $sql = "INSERT INTO Aime (Music_ID, User_ID) VALUES (:id_music, :id_user)";
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
            $sql = "DELETE FROM Aime WHERE Music_ID = :id_music AND User_ID = :id_user";
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