<?php

require("config.php");

if(empty($_SESSION['user']))
{
    header("Location: index.html");
    die("Redirecting to index.html");
}

$projectID=-1;
$set_id=$_POST['si'];
$query = "SELECT a.filter FROM project_files AS a WHERE a.set_id=:set_id AND a.project_id=:project_id";
$query_params = array(':set_id' => $set_id, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
$filter= null;
foreach ($row as $entry) {
	$filter=$entry['filter'];
}

$query = "SELECT COUNT(condition_name) FROM project_conditions WHERE set_id=:set_id AND project_id=:project_id";
$query_params = array(':set_id' => $set_id, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$condCount = 0;
foreach ($row as $entry) {
	$condCount = $entry['COUNT(condition_name)'];
}

$query = "SELECT COUNT(replicate_name) FROM project_replicates WHERE set_id=:set_id AND project_id=:project_id";
$query_params = array(':set_id' => $set_id, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$repCount = 0;
foreach ($row as $entry) {
	$repCount = $entry['COUNT(replicate_name)'];
}

$data = array('filter' => $filter, 'condCount' => $condCount, 'repCount' => $repCount);

echo(json_encode($data));
