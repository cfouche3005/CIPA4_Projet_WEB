<?php
require_once ('constant.php');
function dbConnect() //fonction de connexion à la base de données
  {
    try
    {
      $dsn = 'pgsql:host='.DB_SERVER.';port='.DB_PORT.';dbname='.DB_NAME.';sslmode=require';
      $db = new PDO($dsn, DB_USER, DB_PASSWORD);
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    }
    catch (PDOException $exception)
    {
      error_log('Connection error: '.$exception->getMessage());
      return false;
    }
    return $db;
  }