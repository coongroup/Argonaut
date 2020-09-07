<?php

require('config.php');
$analysis_id=$_POST['ai'];
$projectID=-1;

$query = "SELECT progress FROM hierarchical_clustering_go_analysis WHERE analysis_id=:analysis_id AND project_id=:project_id";
	$query_params = array(':analysis_id' => $analysis_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();

	if(!$row)
	{
		echo (-1);
	}

echo($row['progress']);
