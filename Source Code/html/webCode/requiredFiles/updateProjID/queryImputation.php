<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}


$projectID=-1;
$query = "SELECT a.set_name AS name, a.set_id AS id, b.branch_name AS branch_name, c.impute_missing_values AS impute FROM project_sets AS a JOIN project_branches AS b ON a.branch_id=b.branch_id JOIN project_files AS c ON c.set_id=a.set_id WHERE a.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
foreach ($row as $entry) {
	array_push($data, $entry);
}
echo(json_encode($data));
