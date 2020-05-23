<?php

require('config.php');

$projectID='bW55gC6';

$rowDistance = $_POST['rd'];
$rowLinkage = $_POST['rl'];
$colDistance = $_POST['cd'];
$colLinkage = $_POST['cl'];
$useConds = filter_var($_POST['uc'], FILTER_VALIDATE_BOOLEAN);
$branchID = $_POST['bi'];
$excludedNodes = json_decode($_POST['ex'], true);

$excludedNodeString = "";
foreach ($excludedNodes as $entry) {
	$excludedNodeString .= $entry . '_';
}

$condString = $useConds ? "1" : "0";

$stringRep = $rowDistance . '|' . $rowLinkage . '|' . $colDistance . '|' . $colLinkage . '|' . $condString . '|' . $branchID . '|' . substr($excludedNodeString,0,strlen($excludedNodeString)-1);


$query = "SELECT analysis_id FROM hierarchical_clustering_inputs WHERE string_representation=:stringrep AND project_id=:project_id";
$query_params = array(':stringrep'=> $stringRep, ':project_id'=> $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

if($row)
{
	echo(TRUE);
}
else
{
	echo(FALSE);
}
