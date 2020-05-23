<?php
require("config.php");

$projectID='bW55gC6';
$branchID=$_POST['bi'];

$ids_to_delete = array();

$query = "SELECT analysis_id FROM hierarchical_clustering_inputs WHERE branch_id=:branch_id AND project_id=:project_id";
$query_params = array(':branch_id' => $branchID, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach ($row as $entry) {
	array_push($ids_to_delete, $entry);
}

$query = "DELETE FROM hierarchical_clustering_inputs WHERE branch_id=:branch_id AND project_id=:project_id";
$query_params = array(':branch_id' => $branchID, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "DELETE FROM hierarchical_clustering_results WHERE branch_id=:branch_id AND project_id=:project_id";
$query_params = array(':branch_id' => $branchID, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "DELETE FROM hierarchical_clustering_go_analysis WHERE branch_id=:branch_id AND project_id=:project_id";
$query_params = array(':branch_id' => $branchID, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

foreach ($ids_to_delete as $entry) {
	$currFile = 'heatMap_' . $entry . '.txt';
	unset($currFile);
}
