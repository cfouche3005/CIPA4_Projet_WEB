<?php

require_once('constant.php');
require_once('database.php');
require_once('class/Album.php');
require_once('class/Music.php');
require_once('class/Artist.php');
require_once('class/Playlist.php');
require_once('class/Users.php');
require_once('class/Historique.php');

/*function print_array($array) {
    print("<pre>" . print_r($array, true) . "</pre>");
}*/

ini_set('display_errors', 1); 
error_reporting(E_ALL);
$db = dbConnect();

// Enable CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'];

switch ($method){
    case 'GET':
        switch($path){
            case '/content/albums':
                $response = Album::list_alb($db);
                header('Content-Type: application/json; charset=utf-8');
                header('Cache-control: no-store, no-cache, must-revalidate');
                header('Pragma: no-cache');
                header('HTTP/1.1 200 OK');
                echo json_encode($response);
                break;
            case '/content/album':
                if(isset($_GET['id_album'])){
                    $id_album = $_GET['id_album'];
                    $response = Album::info_album($id_album, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    
                    exit;
                }
                break;
            case '/content/album/random':
                if (isset($_GET['numbers'])) {
                    $numbers = $_GET['numbers'];
                    $response = Album::album_random($numbers, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');

                    exit;
                }
                break;
            case '/user/playlists':
                if(isset($_GET['id_user'])){
                    $id_user = $_GET['id_user'];
                    $response = Playlist::playlist_user($id_user,$db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/albums':
                if(isset($_GET['id_user'])){
                    $id_user = $_GET['id_user'];
                    $response = Album::album_user($id_user,$db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/playlists/like':
                if(isset($_GET['id_music']) && isset($_GET['id_user'])){
                    $id_music = $_GET['id_music'];
                    $id_user = $_GET['id_user'];
                    $response = Music::verif_music_like($id_music, $id_user, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');

                    exit;
                }
                break;
            case '/content/playlist':
                if(isset($_GET['id_playlist'])){
                    $id_playlist = $_GET['id_playlist'];
                    $response = Playlist::get_music_playlist($id_playlist, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/album/like':
                if(isset($_GET['id_user']) && isset($_GET['id_album'])){
                    $id_user = $_GET['id_user'];
                    $id_album = $_GET['id_album'];
                    $response = Users::usr_aime_album_verif($id_user, $id_album, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/historique':
                if(isset($_GET['id_user'])){
                    $id_user = $_GET['id_user'];
                    $response = Historique::recup_hist($id_user, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
        }
        break;
        
    case 'POST':
        switch($path){
            case '/auth/register':
                if(isset($_POST['lastname']) && isset($_POST['surname']) && isset($_POST['mail']) && isset($_POST['password']) && isset($_POST['pseudo']) && isset($_POST['birthdate'])){
                    $lastname = $_POST['lastname'];
                    $firstname = $_POST['surname'];
                    $mail = $_POST['mail'];
                    $password = $_POST['password'];
                    $birthdate = $_POST['birthdate'];
                    $pseudo = $_POST['pseudo'];
                    $response = Users::ajout_usr($mail, $lastname, $firstname, $birthdate, $password, $pseudo, 'tbd', $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;

            case '/auth/login':
                if(isset($_POST['mail']) && isset($_POST['password'])){
                    $mail = $_POST['mail'];
                    $password = $_POST['password'];
                    $response = Users::login_usr($mail, $password, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/playlists':
                if(isset($_POST['nom_playlist']) && isset($_POST['id_user'])){
                    $id_user = $_POST['id_user'];
                    $nom_playlist = $_POST['nom_playlist'];
                    $response = Playlist::creer_playlist($nom_playlist, $id_user, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;

            case '/user/playlists/add':
                if(isset($_POST['id_playlist']) && isset($_POST['id_music'])){

                    $id_playlist = $_POST['id_playlist'];
                    $id_music = $_POST['id_music'];
                    $response = Playlist::add_music_playlist($id_playlist, $id_music, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/playlists/like':
                if(isset($_POST['id_music']) && isset($_POST['id_user'])){
                    $id_music = $_POST['id_music'];
                    $id_user = $_POST['id_user'];
                    $response = Music::ajout_music_like($id_music, $id_user, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');

                    exit;
                }
                break;
            case '/user/album/like':
                if(isset($_POST['id_user']) && isset($_POST['id_album'])){
                    $id_album = $_POST['id_album'];
                    $id_user = $_POST['id_user'];
                    $response = Users::usr_aime_album($id_user, $id_album, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/historique':
                if(isset($_POST['id_user']) && isset($_POST['id_music'])){
                    $id_user = $_POST['id_user'];
                    $id_music = $_POST['id_music'];
                    $response = Historique::add_hist($id_music, $id_user, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/artist':
                if (isset($_POST['id_artist'])){
                    
                    $id_artist = $_POST['id_artist'];
                    $response = Users::info_artiste($id_artist, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate'); 
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            default :
                header('HTTP/1.1 400 Bad Request');
                exit;
        }
        break;
        
    case 'DELETE':
        switch($path) {
            case '/user/playlists':
                if(isset($_GET['id_playlist'])){
                    $id_playlist = $_GET['id_playlist'];
                    $response = Playlist::delete_playlist($id_playlist, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/playlists/delete':
                if(isset($_GET['id_playlist']) && isset($_GET['id_music'])){
                    $id_playlist = $_GET['id_playlist'];
                    $id_music = $_GET['id_music'];
                    $response = Playlist::delete_music_playlist($id_playlist, $id_music, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
            case '/user/playlists/like':
                if(isset($_GET['id_music']) && isset($_GET['id_user'])){
                    $id_music = $_GET['id_music'];
                    $id_user = $_GET['id_user'];
                    $response = Music::delete_music_like($id_music, $id_user, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');

                    exit;
                }
                break;
            case '/user/album/like':
                if(isset($_GET['id_user']) && isset($_GET['id_album'])){
                    $id_user = $_GET['id_user'];
                    $id_album = $_GET['id_album'];
                    $response = Users::usr_aime_album_delete($id_user, $id_album, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad Request');
                    exit;
                }
                break;
        }
        break;

    case 'PUT':
        switch($path) {
            case '/user/playlists':
                parse_str(file_get_contents('php://input'), $_PUT);
                if(isset($_PUT['nom_playlist']) && isset($_PUT['id_playlist'])){
                    $id_playlist = $_PUT['id_playlist'];
                    $nom_playlist = $_PUT['nom_playlist'];
                    $response = Playlist::modifier_playlist($nom_playlist, $id_playlist, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                else{
                    header('HTTP/1.1 400 Bad PUT Request');
                    exit;
                }
                break;
            case '/user/profile':
                parse_str(file_get_contents('php://input'), $_PUT);
                if(isset($_PUT['id_user']) && isset($_PUT['lastname']) && isset($_PUT['surname']) && isset($_PUT['mail']) && isset($_PUT['password']) && isset($_PUT['pseudo']) && isset($_PUT['birthdate']) && isset($_PUT['mp']) && $_PUT['mp'] == 'true'){
                    $id_user = $_PUT['id_user'];
                    $lastname = $_PUT['lastname'];
                    $firstname = $_PUT['surname'];
                    $mail = $_PUT['mail'];
                    $password = $_PUT['password'];
                    $birthdate = $_PUT['birthdate'];
                    $pseudo = $_PUT['pseudo'];
                    $response = User::modifier_usr($id_user, $mail, $lastname, $firstname, $birthdate, $password, $pseudo, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }
                elseif(isset($_PUT['id_user']) && isset($_PUT['lastname']) && isset($_PUT['surname']) && isset($_PUT['mail']) && isset($_PUT['pseudo']) && isset($_PUT['birthdate']) && isset($_PUT['mp']) && $_PUT['mp'] == 'false'){
                    $id_user = $_PUT['id_user'];
                    $lastname = $_PUT['lastname'];
                    $firstname = $_PUT['surname'];
                    $mail = $_PUT['mail'];
                    $birthdate = $_PUT['birthdate'];
                    $pseudo = $_PUT['pseudo'];
                    $response = Users::modifier_usr_sans_mdp($id_user, $mail, $lastname, $firstname, $birthdate, $pseudo, $db);
                    header('Content-Type: application/json; charset=utf-8');
                    header('Cache-control: no-store, no-cache, must-revalidate');
                    header('Pragma: no-cache');
                    header('HTTP/1.1 200 OK');
                    echo json_encode($response);
                }

                else{
                    header('HTTP/1.1 400 Bad PUT Request');
                    exit;
                }
                break;
        }
        break;
}

?>