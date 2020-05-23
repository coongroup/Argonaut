<?php

require('config.php');

$branchID = $_POST['bi'];
$linkages = array();
$linkages[1]='Average';
$linkages[2]='Complete';
$linkages[3]='Single';

$distances = array();
$distances[1]='Euclidean';
$distances[2]='Canberra';
$distances[3]='Cosine';
$distances[4]='Manhattan';
$distances[5]='Maximum';
$distances[6]='Pearson';
$distances[7]='Spearman';

$repDict = array();
$query = "SELECT replicate_id, replicate_name FROM project_replicates WHERE branch_id=:branch_id";
$query_params = array(':branch_id' => $branchID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach ($row as $entry) {
	$repDict[$entry['replicate_id']]=$entry['replicate_name'];
}

//query all conditions --> store as id->name
$condDict = array();
$query = "SELECT condition_id, condition_name FROM project_conditions WHERE branch_id=:branch_id";
$query_params = array(':branch_id' => $branchID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach ($row as $entry) {
	$condDict[$entry['condition_id']]=$entry['condition_name'];
}

$query = "SELECT analysis_id, row_linkage, row_distance, column_linkage, column_distance, running, completed, progress, use_conditions, excluded_nodes FROM hierarchical_clustering_inputs WHERE branch_id=:branch_id AND project_id=:project_id AND failed=0";
	$query_params = array(':branch_id' => $branchID, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	$data = array();

foreach ($row as $entry) {
	$clusterType = $entry['use_conditions']==="1" ? "Condition Clustering" : "Replicate Clustering";
	$rowLink = "Row Linkage: <i>" . $linkages[$entry['row_linkage']] . "</i>";
	$rowDist = "Row Distance: <i>" . $distances[$entry['row_distance']] . "</i>";
	$colLink = "Column Linkage:<i> " . $linkages[$entry['column_linkage']]. "</i>";
	$colDist = "Column Distance:<i> " . $distances[$entry['column_distance']]. "</i>";
	$currAnalysisID = $entry['analysis_id'];
	$excludedNodes = $entry['use_conditions']==="1" ? "Excluded Conditions: " : "Excluded Replicates: ";
	$excludedNodesObj = json_decode($entry['excluded_nodes'], true);
	$excludedNodes .= "<i>";
	$exNodeArray = array();
	foreach ($excludedNodesObj as $node) {
		$entry['use_conditions']==="1" ? $excludedNodes .= $condDict[$node] : $excludedNodes .= $repDict[$node];
		 $exNodeArray[$node] = $entry['use_conditions']==="1" ? $condDict[$node] : $repDict[$node];
		$excludedNodes.=", ";
	}

	$trimmedExcludedNodes = trim($excludedNodes);
	if (count($excludedNodesObj)>0)
	{
		$trimmedExcludedNodes = substr($trimmedExcludedNodes, 0, strlen($trimmedExcludedNodes)-1);
	}

	$trimmedExcludedNodes .= "</i>";
	
	$stringOne = "<b>" . $clusterType . "</b><br>" . "Analysis ID: " . $currAnalysisID . " | " . $trimmedExcludedNodes . " | ";
	//$entry['use_conditions']==="1" ? $stringOne .= "Excluded Conditions: " : $stringOne .= "Excluded Replicates: ";

	$stringTwo = $rowDist . " | " . $rowLink . " | " . $colDist . " | " . $colLink;

	$stringOne .=$stringTwo;

	//$data[(string)$currAnalysisID] = array('analysis_id' => $currAnalysisID, 's1' => $stringOne, 'progress' => $entry['progress']);

	array_push($data, array('analysis_id' => $currAnalysisID, 's1' => $stringOne, 'progress' => $entry['progress']));
}


echo(json_encode($data));
