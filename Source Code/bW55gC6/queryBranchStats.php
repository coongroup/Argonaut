<?php

require("config.php");
if(empty($_SESSION['user']))
{
    header("Location: index.html");
    die("Redirecting to index.html");
}

$branchID = ($_POST['bi']);

$query = "SELECT project_data_summary.*, project_branches.branch_name, project_branches.branch_id FROM 
project_data_summary JOIN project_branches on project_branches.branch_id=project_data_summary.branch_id WHERE project_data_summary.branch_id=:branch_id AND project_data_summary.project_id=:project_id";
$query_params = array(':branch_id' => $branchID, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

$data = array();

foreach ($row as $entry) {
	array_push($data, $entry);
}

echo(json_encode($data));
