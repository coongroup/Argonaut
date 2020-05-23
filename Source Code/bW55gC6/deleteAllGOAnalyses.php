<?php
require("config.php");

$projectID='bW55gC6';
$branchID=$_POST['bi'];

//Delete from go_enrichment_analysis_queue
//Delete from go_enrichment_analysis_inputs
//Delete from go_enrichment_analysis_results

$query = "DELETE FROM go_enrichment_analysis_inputs WHERE project_id=:project_id AND branch_id=:branch_id";
$query_params = array(':project_id' => $projectID, ':branch_id'=> $branchID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "DELETE FROM go_enrichment_analysis_queue WHERE project_id=:project_id AND branch_id=:branch_id";
$query_params = array(':project_id' => $projectID, ':branch_id'=> $branchID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "DELETE FROM go_enrichment_analysis_results WHERE project_id=:project_id AND branch_id=:branch_id";
$query_params = array(':project_id' => $projectID, ':branch_id'=> $branchID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
