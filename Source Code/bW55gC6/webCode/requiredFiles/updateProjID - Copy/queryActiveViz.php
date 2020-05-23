<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant

$projectID=-1;
$query = "SELECT visualization_id, visualization_on FROM project_data_visualizations WHERE project_id=:project_id";
$query_params = array(':project_id'=> $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if(!$row)
{
	$query="INSERT INTO project_data_visualizations (project_id, visualization_id, visualization_on) VALUES (:project_id, 'outlier', 0),(:project_id, 'volcano', 0),(:project_id, 'bar', 0),(:project_id, 'scatter', 0),(:project_id, 'pcacond', 0),(:project_id, 'pcarep', 0)";
	$query_params = array(':project_id'=> $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$query = "SELECT * FROM project_data_visualizations WHERE project_id=:project_id";
	$query_params = array(':project_id'=> $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
}

foreach($row as $entry)
{
	array_push($data, $entry);
}

echo(json_encode($data));
