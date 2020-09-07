<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant

$projectID=-1;
$type = $_POST['t'];

$query = "";

switch ($type)
{
	case "Project":
	$query = "SELECT project_name AS name, project_id AS id FROM projects WHERE project_id=:project_id";
	break;

	case "Branch":
	$query = "SELECT branch_name AS name, branch_id AS id FROM project_branches WHERE project_id=:project_id";
	break;

	case "Set":
	$query = "SELECT a.set_name AS name, a.set_id AS id, b.branch_name AS branch_name FROM project_sets AS a JOIN project_branches AS b ON a.branch_id=b.branch_id WHERE a.project_id=:project_id";
	break;

	case "Condition":
	$query = "SELECT a.condition_name AS name, a.condition_id AS id, b.branch_name AS branch_name, c.set_name AS set_name FROM project_conditions AS a JOIN project_branches AS b ON a.branch_id=b.branch_id JOIN project_sets AS c ON a.set_id=c.set_id WHERE a.project_id=:project_id";
	break;

	case "Replicate":
	$query = "SELECT a.replicate_name AS name, a.replicate_id AS id, b.branch_name AS branch_name, c.set_name AS set_name FROM project_replicates AS a JOIN project_branches AS b ON a.branch_id=b.branch_id JOIN project_sets AS c ON a.set_id=c.set_id WHERE a.project_id=:project_id";
	break;
}

$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
foreach ($row as $entry) {
	switch($type)
	{
		case "Project":
		array_push($data, $entry);
		break;

		case "Branch":
		array_push($data, $entry);
		break;

		case "Set":
		$entry['group'] = "Branch: " . $entry['branch_name'];
		//$entry['name'] = $entry['name'] . " (Branch: " . $entry['branch_name'] . ")";
		array_push($data, $entry);
		break;

		case "Condition":
		$entry['group'] = "Branch: " . $entry['branch_name'] . " | Set: " . $entry['set_name'];
		//$entry['name'] = $entry['name'] . " (Branch: " . $entry['branch_name'] . " | Set: " . $entry['set_name'] . ")";
		array_push($data, $entry);
		break;

		case "Replicate":
		$entry['group'] = "Branch: " . $entry['branch_name'] . " | Set: " . $entry['set_name'];
		//$entry['name'] = $entry['name'] . " (Branch: " . $entry['branch_name'] . " | Set: " . $entry['set_name'] . ")";
		array_push($data, $entry);
		break;
	}
}
echo(json_encode($data));
