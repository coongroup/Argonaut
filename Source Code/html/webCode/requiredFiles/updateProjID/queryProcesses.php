<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant


$projectID=-1;
$query = "SELECT a.task AS task, a.progress AS progress, a.task_creation_time AS time, a.task_completion_time AS time2, a.completed AS completed, b.original_file_name AS name, a.process_id AS id FROM process_queue AS a JOIN project_files AS b ON a.set_id=b.set_id WHERE a.project_id=:project_id AND a.process_failed=0";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

$data = array();
foreach ($row as $entry) {
	$delta = "-1";
	$t1 = strtotime($entry['time']); $t2 = strtotime($entry['time2']);
	$entry['completed']==="1" ? $delta = round(abs($t2 - $t1) / 60,2) : null;
	array_push($data, array('task'=> $entry['task'], 'progress' => $entry['progress'], 'time' => $entry['time'], 'completed' => $entry['completed'], 'name' => $entry['name'], 'delta' => $delta, 'key' => $projectID . $entry['id'], 'failed' => 0));
}

$query = "SELECT a.task AS task, a.progress AS progress, a.task_creation_time AS time, a.task_completion_time AS time2, a.completed AS completed, b.original_file_name AS name, a.process_id AS id FROM process_queue AS a JOIN project_failed_sets AS b ON a.set_id=b.set_id WHERE a.project_id=:project_id AND a.process_failed=1";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

foreach ($row as $entry) {
	$delta = "-1";
	$t1 = strtotime($entry['time']); $t2 = strtotime($entry['time2']);
	$entry['completed']==="1" ? $delta = round(abs($t2 - $t1) / 60,2) : null;
	array_push($data, array('task'=> $entry['task'], 'progress' => $entry['progress'], 'time' => $entry['time'], 'completed' => $entry['completed'], 'name' => $entry['name'], 'delta' => $delta, 'key' => $projectID . $entry['id'], 'failed' => 1));
}

echo(json_encode($data));
