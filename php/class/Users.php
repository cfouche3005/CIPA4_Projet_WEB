<?php

class Users
{
    // Récupère les infos de tous les utilisateurs
    public static function info_usr($conn) {
        try {
            if($conn){
                $sql = 'SELECT * FROM USERS';
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
    // Récupérer les infos d'un utilisateur à partir de son id
    public static function info_usr_by_id($id_user, $conn) {
        try {
            if($conn){
                $sql = 'SELECT * FROM USERS where User_ID = :id_user';
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_user', $id_user);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result;
    }

    // Récupère l'id de l'utilisateur à partir de son mail
    public static function id_usr($mail_user, $conn) {
        try {
            $sql = 'SELECT User_ID FROM USERS WHERE User_Mail = :mail_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':mail_user', $mail_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['User_ID'];
    }
    // Récupère le mail de l'utilisateur à partir de son id
    public static function mail_usr($id_user, $conn) {
        try {
            $sql = 'SELECT User_Mail FROM USERS WHERE User_ID = :id_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['User_Mail'];
    }

    // Récupère le nom de l'utilisateur à partir de son identifiant
    public static function nom_usr($id_user, $conn) {
        try {
            $sql = 'SELECT User_Name FROM USERS WHERE User_ID = :id_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['User_Name'];
    }

    // Récupère le prénom de l'utilisateur à partir de son identifiant
    public static function prenom_usr($id_user, $conn) {
        try {
            $sql = 'SELECT User_Surname FROM USERS WHERE User_ID = :id_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['User_Surname'];
    }

    // Récupère l'âge' de l'utilisateur à partir de son identifiant
    // Note: The new schema has User_birthdate, not age. We can calculate age or return birthdate.
    // Returning birthdate for now as age_user column doesn't exist.
    public static function age_usr($id_user, $conn) {
        try {
            $sql = 'SELECT User_birthdate FROM USERS WHERE User_ID = :id_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['User_birthdate'];
    }

    // Récupère le mdp de l'utilisateur à partir de son identifiant
    public static function mdp_usr($id_user, $conn) {
        try {
            $sql = 'SELECT User_Password FROM USERS WHERE User_ID = :id_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['User_Password'];
    }
    // Fonction qui permet de se connecter 
    public static function login_usr($mail_user, $mdp_user, $conn) {
        try {
            $mail_exist= 'SELECT COUNT(*) as count FROM USERS WHERE User_Mail = :mail_user';
            $stmt = $conn->prepare($mail_exist);
            $stmt->bindParam(':mail_user', $mail_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] == 1) {

                    //récupère le mp crypté present ds la base de donnée selon l'email 
                    $request = 'SELECT User_Password from USERS where User_Mail = :mail_user';
                    $statement = $conn->prepare($request);
                    $statement->bindParam(':mail_user',$mail_user);
                    $statement->execute();
                    $mp_crypt_bd = $statement->fetch(PDO::FETCH_ASSOC);
            
                    //verifie si mp entrer est mp crypt de la bd
                    $checkMp = password_verify($mdp_user,$mp_crypt_bd['User_Password']);   //attention verify prend que string
                    if($checkMp){
                        $sql = 'SELECT * FROM USERS WHERE User_Mail = :mail_user';
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':mail_user', $mail_user);
                        $stmt->execute();
                        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        return $result;

                    }
                    else {
                        return false;
                    }
            }
            else  {
                return false;
            }
        }
            
        catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        
    }

    // Récupère le pseudo de l'utilisateur à partir de son identifiant
    public static function pseudo_usr($id_user, $conn) {
        try {
            $sql = 'SELECT User_Pseudo FROM USERS WHERE User_ID = :id_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['User_Pseudo'];
    }

    // Récupère le photo de l'utilisateur à partir de son identifiant
    public static function photo_usr($id_user, $conn) {
        try {
            $sql = 'SELECT User_Image FROM USERS WHERE User_ID = :id_user';
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
        return $result['User_Image'];
    }
    //Fonction qui permet de se créer un compte 
    public static function ajout_usr($mail_user, $nom_user, $prenom_user, $date_naissance, $mdp_user, $pseudo_user, $photo_user, $conn) {
        try {
            // Using UUID for User_ID since it's VARCHAR(50)
            $user_id = uniqid();

            $sql = 'INSERT INTO USERS (User_ID, User_Mail, User_Name, User_Surname, User_birthdate, User_Password, User_Pseudo, User_Image) VALUES (:user_id, :mail_user, :nom_user, :prenom_user, :date_naissance, :mdp_user, :pseudo_user, :photo_user)';
            
            //vérification si le mail n'existe pas déjà 
            $mail_exist= 'SELECT COUNT(*) as count FROM USERS WHERE User_Mail = :mail_user';
            $stmt = $conn->prepare($mail_exist);
            $stmt->bindParam(':mail_user', $mail_user);
            $stmt->execute();
            $resultMail = $stmt->fetch(PDO::FETCH_ASSOC);

            if($resultMail['count']>=1){
                return "mail-exist";
            }
            else{
                $stmt4 = $conn->prepare($sql);
                $stmt4->bindParam(':user_id', $user_id);
                $stmt4->bindParam(':mail_user', $mail_user);
                $stmt4->bindParam(':nom_user', $nom_user);
                $stmt4->bindParam(':prenom_user', $prenom_user);
                $stmt4->bindParam(':date_naissance', $date_naissance);
                $mdp_user= password_hash($mdp_user, PASSWORD_BCRYPT);
                $stmt4->bindParam(':mdp_user', $mdp_user);
                $stmt4->bindParam(':pseudo_user', $pseudo_user);
                $stmt4->bindParam(':photo_user', $photo_user);
                $stmt4->execute();
                
                //on crée une playlist par défaut "Favoris" pour l'utilisateur :
                // Playlist_ID needs to be generated too
                $playlist_id = uniqid();
                $playlistFav = 'INSERT INTO Playlist (Playlist_ID, Playlist_Name, Playlist_Creation_Date, User_ID) VALUES (:playlist_id, :nom_playlist, CURRENT_DATE, :user_id)';
                $stmt2 = $conn->prepare($playlistFav);
                $nom_playlist = "Favoris";
                $stmt2->bindParam(':playlist_id', $playlist_id);
                $stmt2->bindParam(':nom_playlist', $nom_playlist);
                $stmt2->bindParam(':user_id', $user_id);
                $stmt2->execute();
                
            }
        
        }catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    return true;
    }
    //Fonction qui permet de modifier les informations de son compte
    public static function modifier_usr($id_user, $mail_user, $nom_user, $prenom_user, $date_naissance, $mdp_user, $pseudo_user, $conn) {
        try {
            
            $sql = 'UPDATE USERS SET User_Mail = :mail_user, User_Name = :nom_user, User_Surname = :prenom_user, User_birthdate = :date_naissance, User_Password = :mdp_user, User_Pseudo = :pseudo_user WHERE User_ID = :id_user';
            
            //vérification si le mail n'existe pas déjà (sauf si c'est le sien)
            $mail_exist= 'SELECT COUNT(*) as count FROM USERS WHERE User_Mail = :mail_user AND User_ID != :id_user';
            $stmt = $conn->prepare($mail_exist);
            $stmt->bindParam(':mail_user', $mail_user);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $resultMail = $stmt->fetch(PDO::FETCH_ASSOC);

            if($resultMail['count']>=1){
                return "mail-exist";
            }
            else {
                $stmt4 = $conn->prepare($sql);
                $stmt4->bindParam(':mail_user', $mail_user);
                $stmt4->bindParam(':nom_user', $nom_user);
                $stmt4->bindParam(':prenom_user', $prenom_user);
                $stmt4->bindParam(':date_naissance', $date_naissance);
                $mdp_user= password_hash($mdp_user, PASSWORD_BCRYPT);
                $stmt4->bindParam(':mdp_user', $mdp_user);
                $stmt4->bindParam(':pseudo_user', $pseudo_user);
                $stmt4->bindParam(':id_user', $id_user);
                $stmt4->execute();
            }
            return Users::info_usr_by_id($id_user, $conn);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }
    //Fonction qui permet de modifier les informations de son compte sans modifier le mot de passe
    public static function modifier_usr_sans_mdp($id_user, $mail_user, $nom_user, $prenom_user, $date_naissance, $pseudo_user, $conn) {
        try {
            
            $sql = 'UPDATE USERS SET User_Mail = :mail_user, User_Name = :nom_user, User_Surname = :prenom_user, User_birthdate = :date_naissance, User_Pseudo = :pseudo_user WHERE User_ID = :id_user';
            
            $mail_exist= 'SELECT COUNT(*) as count FROM USERS WHERE User_Mail = :mail_user AND User_ID != :id_user';
            $stmt = $conn->prepare($mail_exist);
            $stmt->bindParam(':mail_user', $mail_user);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->execute();
            $resultMail = $stmt->fetch(PDO::FETCH_ASSOC);

            if($resultMail['count']>=1){
                return "mail-exist";
            }
            else{
                $stmt4 = $conn->prepare($sql);
                $stmt4->bindParam(':mail_user', $mail_user);
                $stmt4->bindParam(':nom_user', $nom_user);
                $stmt4->bindParam(':prenom_user', $prenom_user);
                $stmt4->bindParam(':date_naissance', $date_naissance);
                $stmt4->bindParam(':pseudo_user', $pseudo_user);
                $stmt4->bindParam(':id_user', $id_user);
                $stmt4->execute();
            }
            return Users::info_usr_by_id($id_user, $conn);
        } catch (PDOException $exception) {
            error_log('Connection error: ' . $exception->getMessage());
            return false;
        }
    }
    //Fonction qui permet de supprimer un utilisateur
    public static function delete_usr($id_user, $conn)
    {
        try
        {
          $request = 'DELETE FROM USERS WHERE User_ID=:id_user';
          $statement = $conn->prepare($request);
          $statement->bindParam(':id_user', $id_user);
          $statement->execute();
        }
        catch (PDOException $exception)
        {
          error_log('Request error: '.$exception->getMessage());
          return false;
        }
        return true;
      }

    //Fonction qui permet de savoir quelles albums un utilisateur à aimé afin de pouvoir l'ajouter à sa bibliothèque
    // Note: Schema doesn't have Aime_Album table. Assuming feature is dropped or schema is incomplete.
    // Returning false/empty for now.
    public static function usr_aime_album($id_user, $id_album, $conn) {
        return false;
    }

    // Fonction qui permet de savoir si un utilisateur a déjà aimé un album
    public static function usr_aime_album_verif($id_user, $id_album, $conn) {
        return false;
    }
    //Fonction qui permet d'enlever un album de la liste des albums aimés par un utilisateur
    public static function usr_aime_album_delete($id_user, $id_album, $conn) {
        return false;
    }
}