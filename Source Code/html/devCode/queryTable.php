<?php

require("config.php");

//if (empty($_SESSION['user'])) {

   // header("Location: index.html");

  //  die("Redirecting to index.html");

//}

$dbConnection = new PDO('mysql:dbname=' . $dbname . ';host=' . $host . ';charset=utf8', $username, $password);

$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$descriptor = $_POST['d'];


$stmt = $dbConnection->prepare("SELECT * FROM data_query_terms WHERE LOWER(query_term_text) LIKE :descriptor AND project_id=-1 ORDER BY query_term_text");

$stmt->bindValue(':descriptor', '%' . $descriptor . '%');

$stmt->execute();

$resultset = $stmt->fetchALL(PDO::FETCH_ASSOC);

echo(json_encode($resultset));

?>