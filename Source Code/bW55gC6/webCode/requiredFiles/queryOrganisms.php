<?php

require("config.php");
if(empty($_SESSION['user']))
{
    //header("Location: index.html");
    die("Redirecting to index.html");
}

$query = "SELECT organism_name AS name, organism_id AS id FROM organisms";
$stmt = $db->prepare($query);
$result = $stmt->execute();
$row = $stmt->fetchAll();
echo(json_encode($row));

