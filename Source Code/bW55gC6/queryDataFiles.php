<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

$query = "SELECT a.set_name, b.branch_name, c.truncated_file_name, c.set_id FROM project_files AS c JOIN project_sets AS a ON a.set_id=c.set_id JOIN project_branches AS b ON b.branch_id=c.branch_id WHERE c.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
foreach ($row as $entry) {
	$currData = array('display'=> $entry['set_name'] . " (Branch: " . $entry['branch_name'] . ")", "id" => $entry['set_id'], "branch" => $entry['branch_name'], 'file'=>$entry['truncated_file_name']);
	array_push($data, $currData);
}
echo(json_encode($data));
