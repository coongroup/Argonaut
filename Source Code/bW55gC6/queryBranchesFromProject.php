<?php

require("config.php");

if(empty($_SESSION['user']))
{
    die();
}

$projectID='bW55gC6';
$query = "SELECT project_branches.branch_name, project_branches.branch_id FROM  project_branches WHERE project_branches.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

$data = array();

foreach ($row as $entry) {
	array_push($data, $entry);
}

echo(json_encode($data));
