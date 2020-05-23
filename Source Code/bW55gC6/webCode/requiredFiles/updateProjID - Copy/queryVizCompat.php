<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant

$projectID=-1;
$query = "SELECT * FROM project_replicates WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

$data = array();

foreach ($row as $entry) {
	$cond_key = $entry['condition_id'] + "_" + $entry['branch_id'];
	if(!array_key_exists($entry['branch_id'], $data))
	{
		$data[$entry['branch_id']]=array();
	}
	if(!array_key_exists($entry['condition_id'], $data[$entry['branch_id']]))
	{
		$data[$entry['branch_id']][$entry['condition_id']]=array();
	}
	array_push($data[$entry['branch_id']][$entry['condition_id']], $entry['replicate_id']);
}

$single_branch_cond_mult_rep = 0;
$max_cond_count = 0;

foreach ($data as $key => $value) {
	//$key = branch, value = data in branch
	count($value) > $max_cond_count ? $max_cond_count = count($value) : null;
	$tmp_cond_mult_rep_count = 0;
	foreach ($value as $entry) {
		count($entry)>=2 ? $tmp_cond_mult_rep_count++ : null;
	}
	$tmp_cond_mult_rep_count > $single_branch_cond_mult_rep ? $single_branch_cond_mult_rep = $tmp_cond_mult_rep_count : null;
}

//volcano needs to have 2 conditions with more than 2 replicates.
$returnData = array('volcano' => $single_branch_cond_mult_rep >=2, 'bar' => $single_branch_cond_mult_rep >=2, 'scatter' => $single_branch_cond_mult_rep >=2, 'pcacond' => $max_cond_count >= 2, 'pcarep' => count($row)>=2, 'outlier' => $max_cond_count>=2);
echo(json_encode($returnData));

//bar chart needs 2+ conditions

//scatter plot, 2+ conditions

//pca conditions needs 3+ conditions

//pca replicates needs 3+ replicates
