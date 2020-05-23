<?php
require("config.php");
if(empty($_SESSION['user']))
{
	header("Location: main.php");
	die("Redirecting to main.php");
}

//Confirm that the user can in fact add data to this project otherwise redirect.
$projectID='bW55gC6';

$query = "SELECT * FROM project_temporary_file_headers WHERE uploader_user_id=" . $_SESSION['user'] . " AND project_id='" . $projectID . "'";
$stmt = $db->prepare($query);
$result = $stmt->execute();
$row = $stmt->fetch();
if(!$row)
{
	header("Location: main.php");
	die("Redirecting to main.php");
}

$file_id=$row['temporary_file_id'];
$og_file_name=$row['original_file_name'];
$file_name=$row['file_name'];
$upload_time=$row['upload_time'];
$identifier=$row['identifier'];
$feature_descriptors =$row['feature_descriptors'];
$quant = $row['quant'];
$log2 = $row['log2_transform'];
$impute=$row['impute_missing_values'];
$filter=$row['filter'];
$branch_id=$row['branch_id'];
$set_name=$row['set_name'];
$delimiter=$row['delimiter'];


$full_file_path = 'UploadFolder/' . $file_name;

//check that the file exists 
if (!file_exists($full_file_path))
{
	 throw new RuntimeException('File not found!');
}

//2d array with all file data will go here
$file_array = array();

//store all error messages here
$critical_errors = array();
$noncritical_errors = array();

//open and read the file here
switch($delimiter)
{
	case "TAB": $file_array= array_map(function($v){return str_getcsv($v, "\t");}, file($full_file_path)); break;
	case "COMMA": $file_array=array_map(function($v){return str_getcsv($v, ",");}, file($full_file_path)); break;
	case "WHITESPACE": $file_array=array_map(function($v){return str_getcsv($v, " ");}, file($full_file_path)); break;
	case "SEMICOLON": $file_array=array_map(function($v){return str_getcsv($v, ";");}, file($full_file_path)); break;
}

//check for duplicate headers here (critical error)
$header_array = array_count_values($file_array[0]);
$header_array_map = array();
$count = 0;
foreach (array_keys($header_array) as $header) {
	$header_array[$header] >1 ? array_push($critical_errors, "Duplicate header: " . $header) : null;
	$header_array_map[$header]=$count;
	$count++;
}

//open up json arrays for id, feature descriptors, and quant here
$unique_id_header_array = json_decode($identifier, true);
$feature_descriptor_header_array = json_decode($feature_descriptors, true);
$quant_header_array = json_decode($quant, true);

//check if each header is present in $header_array
foreach ($unique_id_header_array as $unique_id_header) {
	array_key_exists($unique_id_header['header'], $header_array) ? null : array_push($critical_errors, "Header not found: " . $unique_id_header['header']);
}
foreach ($feature_descriptor_header_array as $feature_header) {
	array_key_exists($feature_header['header'], $header_array) ? null : array_push($critical_errors, "Header not found: " . $feature_header['header']);
}
foreach ($quant_header_array as $quant_header) {
	array_key_exists($quant_header['header'], $header_array) ? null : array_push($critical_errors, "Header not found: " . $quant_header['header']);
}

//TO-DO: break out here if there are redundant headers or if any header cannot be found!
if (count($critical_errors)>0)
{
	$return_array = array();
	$return_array['critical'] = $critical_errors;
	$return_array['noncritical'] = $noncritical_errors;
	echo json_encode($return_array);
	throw new RuntimeException('Critical Errors!');
}

//check all unique identifiers (empty fields are noted as critical, redundant are non-critical)
$unique_identifier_array = array();
$unique_identifier_index = $header_array_map[$unique_id_header_array[0]['header']];

for ($i = 1; $i < count($file_array); $i++)
{
	$curr_id = $file_array[$i][$unique_identifier_index];
	array_key_exists($curr_id, $unique_identifier_array) ? array_push($noncritical_errors, "Redundant Identifier: " . $curr_id . " on line " . $i) : $unique_identifier_array[$curr_id]=$i;
	IsNullOrEmptyString($curr_id) ? array_push($critical_errors, "Empty identifier on line " . $i) : null;
}

//check that each quantitative column has at least one numeric value present
foreach ($quant_header_array as $quant_header) {
	$quant_header_index = $header_array_map[$quant_header['header']];
	$has_quant_val = false;
	for ($i = 1; $i < count($file_array); $i++)
	{
		is_numeric($file_array[$i][$quant_header_index]) ? $has_quant_val=false : null;
		if($has_quant_val) { break; }
	}
	!$has_quant_val ? array_push($critical_errors, "No quantitative values found in column '" . $quant_header['header'] . "'") : null;
}

$return_array = array();
$return_array['critical'] = $critical_errors;
$return_array['noncritical'] = $noncritical_errors;

echo (json_encode($return_array));


function IsNullOrEmptyString($question){
    return (!isset($question) || trim($question)==='');
}

?>
