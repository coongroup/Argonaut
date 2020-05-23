<?php

require("config.php");
//if(empty($_SESSION['user']))
{
    //header("Location: index.html");
    //die("Redirecting to index.html");
}

$projectID=-1;
$query = "SELECT unique_identifier_id, unique_identifier_text FROM data_unique_identifiers WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		array_push($data, $entry);
	}
}
echo(json_encode($data));

