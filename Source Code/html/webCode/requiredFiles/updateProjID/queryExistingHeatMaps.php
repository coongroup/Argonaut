<?php

require('config.php');

$projectID=-1;
$branchID=$_POST['bi'];

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

//get branch id
//query all replicates --> store as id->name
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

$query = "SELECT use_conditions, excluded_nodes, included_nodes, row_linkage, row_distance, column_linkage, column_distance, analysis_id FROM hierarchical_clustering_inputs WHERE project_id=:project_id AND branch_id=:branch_id AND completed=1 AND failed=0";
$query_params = array(':project_id' => $projectID, ':branch_id' => $branchID);
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
	$incNodeArray = array();
	foreach ($excludedNodesObj as $node) {
		$entry['use_conditions']==="1" ? $excludedNodes .= $condDict[$node] : $excludedNodes .= $repDict[$node];
		 $exNodeArray[$node] = $entry['use_conditions']==="1" ? $condDict[$node] : $repDict[$node];
		$excludedNodes.=", ";
	}
	foreach(json_decode($entry['included_nodes'], true) as $node)
	{
		$incNodeArray[$node]="";
	}

	$trimmedExcludedNodes = trim($excludedNodes);
	if (count($excludedNodesObj)>0)
	{
		$trimmedExcludedNodes = substr($trimmedExcludedNodes, 0, strlen($trimmedExcludedNodes)-1);
	}

	$trimmedExcludedNodes .= "</i>";
	
	$fullString = "Analysis ID: " . $currAnalysisID . " | " . $trimmedExcludedNodes . "<br>" . $rowDist . "  |  " . $rowLink . "<br>" . $colDist . "  |  " . $colLink /*. "<br>" . $trimmedExcludedNodes*/;
	$shortText = "Row Distance: " .  $distances[$entry['row_distance']] . " | Row Linkage: " . $linkages[$entry['row_linkage']] . "...";

	array_push($data, array('id' => $entry['analysis_id'], 'rowLink'=>$entry['row_linkage'],
	 'rowDist'=> $entry['row_distance'], 'colLink'=> $entry['column_linkage'], 'colDist'=> $entry['column_distance'],
	  'exNodes' =>json_encode($exNodeArray), 'type' => $clusterType, 'text'=>$fullString, 'short'=>$shortText, 'useConds' => $entry['use_conditions'], 'incNodes' => json_encode($incNodeArray)));
}

echo(json_encode($data));
