<?php

require("config.php");

if(empty($_SESSION['user']))
{
    header("Location: index.html");
    die("Redirecting to index.html");
}

$set_id=$_POST['si'];
$query = "SELECT organism_id, standard_id_column, standard_id_type, identifier, feature_descriptors FROM project_files WHERE set_id=:set_id AND project_id=:project_id";
$query_params = array(':set_id' => $set_id, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
foreach ($row as $entry) {
	array_push($data, $entry);
}

echo(json_encode($data));
