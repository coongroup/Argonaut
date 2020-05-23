<?php

require("config.php");
if(empty($_SESSION['user']))
{
    //header("Location: index.html");
    die("Redirecting to index.html");
}

$query = "SELECT identifier_name AS name, identifier_id AS id FROM standard_molecular_identifiers";
$stmt = $db->prepare($query);
$result = $stmt->execute();
$row = $stmt->fetchAll();
echo(json_encode($row));

