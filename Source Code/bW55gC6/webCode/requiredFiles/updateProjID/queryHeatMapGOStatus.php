<?php

require('config.php');

$analysis_id = $_POST['ai'];
$branch_id=$_POST['bi'];
$cluster_count=$_POST['cc'];
$projectID=-1;

//check if hierarchical_clustering_go_analysis has a matching entry
//otherwise check if any molecules on branch have mapped go terms

$returnArray = array(':mapped' => false, ':available'=>false);

$query = "SELECT * FROM hierarchical_clustering_go_analysis WHERE analysis_id=:analysis_id AND cluster_count=:cluster_count";
$query_params = array(':analysis_id' => $analysis_id, ':cluster_count' => $cluster_count);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

if(!$row)
{
	$query = "SELECT COUNT(*) FROM project_files WHERE standard_id_type!=-1 AND branch_id=:branch_id AND project_id=:project_id";
	$query_params = array(':branch_id' => $branch_id, ':project_id'=> $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if($row['COUNT(*)']!=="0")
	{
		$returnArray['mapped']=true;
	}
	echo(json_encode($returnArray));
	exit();
}
else
{
	$returnArray['mapped']=true;
	$returnArray['available']=true;
	echo(json_encode($returnArray));
	exit();
}
