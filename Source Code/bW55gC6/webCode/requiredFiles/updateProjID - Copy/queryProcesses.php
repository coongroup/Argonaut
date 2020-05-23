<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant


$projectID=-1;
$query = "SELECT a.task AS task, a.task_creation_time AS time, a.completed AS completed, b.original_file_name AS name FROM process_queue AS a JOIN project_files AS b ON a.set_id=b.set_id WHERE a.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
echo(json_encode($row));
