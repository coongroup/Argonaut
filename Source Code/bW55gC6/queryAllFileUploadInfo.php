<?php
require("config.php");

if(empty($_SESSION['user']))

{
   header("Location: index.html");
    die("Redirecting to index.html");
}

$organism_array = array();
$organism_array[-1]="Not Specified";

$id_type = array();
$id_type[-1]="Not Specified";

$query = "SELECT * FROM organisms";
$stmt = $db->prepare($query); 
$result = $stmt->execute(); 
$row = $stmt->fetchAll();
foreach ($row as $entry) {
	$organism_array[(int)$entry['organism_id']] = $entry['organism_name'];
}

$query = "SELECT * FROM standard_molecular_identifiers";
$stmt = $db->prepare($query); 
$result = $stmt->execute(); 
$row = $stmt->fetchAll();
foreach ($row as $entry) {
	$id_type[(int)$entry['identifier_id']] = $entry['identifier_name'];
}


$queryText = "SELECT a.set_name, b.impute_missing_values, b.log2_transform, b.organism_id, b.standard_id_column, b.standard_id_type, b.filter, b.upload_time, b.original_file_name FROM project_sets AS a JOIN project_files AS b ON a.file_id=b.file_id WHERE a.project_id=:project_id";
$queryParams = array(':project_id' => $projectID);
$stmt = $db->prepare($queryText); 
$result = $stmt->execute($queryParams); 
$row = $stmt->fetchAll();
$data = array(); 
foreach($row AS $entry)
{
	$organism = $organism_array[(int)$entry['organism_id']];
	$has_organism = (int)$entry['organism_id']!==-1;
	$standard_id_type = $id_type[(int)$entry['standard_id_type']];
	$standard_id_column = $entry['standard_id_column'];
	empty($standard_id_column) ? $standard_id_column="Not Specified" : null;
	$filterObj = json_decode($entry['filter'], true);
	$filter = "No Filter Applied.";
	$time=$entry['upload_time'];
	$filename = $entry['original_file_name'];
	if ($filterObj['type']==="TOTAL")
	{
		//must be seen in at least $p1 % of replicates
		$filter = "Retain values observed in at least " . $filterObj['p1'] . "% of replicates.";
	}
	if ($filterObj['type']==="COND")
	{
		//must be seen in at least p1 % of replicates in at least p2 conditions
		$filter = "Retain values observed in at least " . $filterObj['p1'] . "% of replicates in at least " . $filterObj['p2'] . " conditions."; 
	}

	$imputation = $entry['impute_missing_values']===1 || $entry['impute_missing_values']==="1" ? "True" : "False";
	$log2 = $entry['log2_transform']===1 || $entry['log2_transform']==="1" ? "True" : "False";
	$tmpArray = array('SetName'=> $entry['set_name'], 'Impute' => $imputation, 'Log2' => $log2, 'Filter' => $filter, 'Organism' => $organism, 'StandardIDColumn' => $standard_id_column, 'StandardIDType' => $standard_id_type, 'HasOrganism' => $has_organism, 'date' => $time, 'File' => $filename, 'failed' => 0);
	array_push($data, $tmpArray);
}

$queryText = "SELECT a.set_name, a.impute_missing_values, a.log2_transform, a.organism_id, a.standard_id_column, a.standard_id_type, a.filter, a.upload_time, a.original_file_name FROM project_failed_sets AS a WHERE a.project_id=:project_id";
$queryParams = array(':project_id' => $projectID);
$stmt = $db->prepare($queryText); 
$result = $stmt->execute($queryParams); 
$row = $stmt->fetchAll();
foreach($row AS $entry)
{
	$organism = $organism_array[(int)$entry['organism_id']];
	$has_organism = (int)$entry['organism_id']!==-1;
	$standard_id_type = $id_type[(int)$entry['standard_id_type']];
	$standard_id_column = $entry['standard_id_column'];
	empty($standard_id_column) ? $standard_id_column="Not Specified" : null;
	$filterObj = json_decode($entry['filter'], true);
	$filter = "No Filter Applied.";
	$time=$entry['upload_time'];
	$filename = $entry['original_file_name'];
	if ($filterObj['type']==="TOTAL")
	{
		//must be seen in at least $p1 % of replicates
		$filter = "Retain values observed in at least " . $filterObj['p1'] . "% of replicates.";
	}
	if ($filterObj['type']==="COND")
	{
		//must be seen in at least p1 % of replicates in at least p2 conditions
		$filter = "Retain values observed in at least " . $filterObj['p1'] . "% of replicates in at least " . $filterObj['p2'] . " conditions."; 
	}

	$imputation = $entry['impute_missing_values']===1 || $entry['impute_missing_values']==="1" ? "True" : "False";
	$log2 = $entry['log2_transform']===1 || $entry['log2_transform']==="1" ? "True" : "False";
	$tmpArray = array('SetName'=> $entry['set_name'], 'Impute' => $imputation, 'Log2' => $log2, 'Filter' => $filter, 'Organism' => $organism, 'StandardIDColumn' => $standard_id_column, 'StandardIDType' => $standard_id_type, 'HasOrganism' => $has_organism, 'date' => $time, 'File' => $filename, 'failed' => 1);
	array_push($data, $tmpArray);
}

usort($data, "sortFunction");
echo(json_encode($data));

function sortFunction( $a, $b ) {
    return strtotime($b['date']) - strtotime($a['date']);
}
