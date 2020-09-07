<?php 

    // These variables define the connection information for your MySQL database

    // preconfigured account: 
	// $username = "docker_user";
    // $password = "2ZLy.evYa[qu=$-6";
    
	// you really should update these if you want a secure backend.
	// otherwise, just uncomment the default account above.
    $username = "your_username";
    $password = "your_password";
    $host = "localhost"; 
    
    // preconfigured docker database name
    // $dbname = "docker_db";
    $dbname = "docker_db";
    
    $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::MYSQL_ATTR_LOCAL_INFILE => true); 
    try { $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options); } 
    catch(PDOException $ex){ die("Failed to connect to the database: " . $ex->getMessage());} 
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
    header('Content-Type: text/html; charset=utf-8'); 
    session_start(); 
?>