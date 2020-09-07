<?php

require('config.php');

$analysis_id = $_POST['ai'];
$branch_id=$_POST['bi'];
$numClusters = $_POST['nc'];
$projectID=-1;

if (!is_numeric($numClusters))
{
	echo("Invalid cluster count provided! Please update this value and resubmit your analysis request.");
}

//do check here whether 

$query = "SELECT * FROM hierarchical_clustering_go_analysis WHERE analysis_id=:analysis_id AND cluster_count=:cluster_count AND project_id=:project_id AND running=1";
$query_params = array(':analysis_id' => $analysis_id, ':cluster_count' => $numClusters, ':project_id'=>$projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if($row)
{
	echo("The specified analysis is currently running.");
	exit();
}

$query = "INSERT INTO hierarchical_clustering_go_analysis (analysis_id, cluster_count, project_id) VALUES (:analysis_id, :cluster_count, :project_id)";
$query_params = array(':analysis_id' => $analysis_id, ':cluster_count' => $numClusters, ':project_id'=>$projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$ch = curl_init();
 
curl_setopt($ch, CURLOPT_URL,  "127.0.0.1/DV/" . $projectID . "/calculateGOClusterEnrichments.php");
//curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 1);
 
curl_exec($ch);
curl_close($ch);

echo("Gene Ontology (GO) enrichment analysis of the " . $numClusters . " clusters displayed here has started on our remote servers. The results of this analysis will become available after processing and you can monitor progress here.");
