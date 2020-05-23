<?php
require("config.php");

$projectID=-1;
$analysisID=$_POST['ai'];

//Get process id from go_enrichment_analysis_inputs

$query = "SELECT process_id FROM go_enrichment_analysis_inputs WHERE project_id=:project_id AND analysis_id=:analysis_id";
$query_params = array(':project_id' => $projectID, ':analysis_id'=>$analysisID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

if(!$row)
{
	exit();
}

$processID=$row['process_id'];

$query = "DELETE FROM go_enrichment_analysis_inputs WHERE process_id=:process_id";
$query_params = array(':process_id'=>$processID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "DELETE FROM go_enrichment_analysis_queue WHERE process_id=:process_id";
$query_params = array(':process_id'=>$processID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "DELETE FROM go_enrichment_analysis_results WHERE process_id=:process_id";
$query_params = array(':process_id'=>$processID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
