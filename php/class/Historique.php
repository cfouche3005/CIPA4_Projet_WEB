<?php

class Historique
{
    // Ajoute la music écouter à la table historique
    public static function add_hist($id_music, $id_user, $conn) {
        try {

            $sql = 'INSERT INTO derniere_ecoute (id_user, id_music, date_ecoute, heure_ecoute) VALUES (:id_user, :id_music, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)';
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

    // Récupère l'historique d'un user
    public static function recup_hist($id_user, $conn) {
        try {

            $sql = 'SELECT de.id_music, de.date_ecoute FROM derniere_ecoute de
                JOIN users u ON u.id_user=de.id_user
                WHERE de.id_user=:id_user
                ORDER BY de.date_ecoute DESC 
                LIMIT 10';
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