<?php
require("config.php");
if(empty($_SESSION['user']))
{
	header("Location: main.php");
	die("Redirecting to main.php");
}

//Confirm that the user can in fact add data to this project otherwise redirect.
$projectID=-1;
$query = "SELECT 1 FROM project_permissions WHERE permission_level>=2 AND user_id=:user_id AND project_id=:project_id";
$query_params = array(':user_id' => $_SESSION['user'],
	':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	header("Location: main.php");
	die("Redirecting to main.php");
}

//query current temporary file, if it doesn't exist then return error message
$query = "SELECT * FROM project_temporary_files WHERE uploader_user_id=:user_id AND project_id=:project_id";
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	header("Location: main.php");
	die("Redirecting to main.php");
}

$query = "DELETE FROM project_temporary_file_headers WHERE uploader_user_id=:user_id AND project_id=:project_id";
try
{
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params); }
	catch (PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}

	$file_id=$row['temporary_file_id'];
	$og_file_name=$row['original_file_name'];
	$file_name=$row['file_name'];
	$upload_time=$row['upload_time'];

//get all POST variables
	$identifier=$_POST['id'];
	$feature_descriptors=$_POST['fd'];
	$quant=$_POST['q'];
	$log2=$_POST['log2'];
	$impute=$_POST['imp'];
	$filter=$_POST['filter'];
	$branch_id=$_POST['bi'];
	$set_name=$_POST['sn'];
	$delimiter=$_POST['delim'];

//Check all post variables to make sure they are kosher here
$valid_inputs = CheckInputs($identifier, $feature_descriptors, $quant, $log2, $impute, $filter, $branch_id, $set_name, $delimiter, $projectID, $db);
if(!$valid_inputs)
{
	header('HTTP/1.1 500 Internal Server Error');
		throw new RuntimeException('Failed to add file!');
		//trigger_error("Failed to add file!");
		die();
}


//add POST variables to temporary file headers table (note: once the file has been validated you need to move this information to permanent file table so it cannot be deleted or overwritten).
	$argLine = "INSERT INTO project_temporary_file_headers (
		uploader_user_id,project_id,temporary_file_id,original_file_name,file_name,upload_time,identifier,feature_descriptors,quant,log2_transform,impute_missing_values,filter,branch_id,set_name,delimiter)
VALUES (:uploader_user_id,:project_id,:temporary_file_id,:original_file_name,:file_name,:upload_time,:identifier,:feature_descriptors,:quant,:log2_transform,:impute_missing_values,:filter,:branch_id,:set_name,:delimiter)";
$query_params = array(
	':uploader_user_id' => $_SESSION['user'],':project_id' => $projectID,':temporary_file_id' => $file_id,':original_file_name' => $og_file_name,':file_name' => $file_name,':upload_time' => $upload_time,':identifier' => $identifier,':feature_descriptors' => $feature_descriptors,
	':quant' => $quant,':log2_transform' => $log2,
	':impute_missing_values' => $impute,':filter' => $filter,':branch_id' => $branch_id,':set_name' => $set_name,':delimiter' => $delimiter
	); 
try
{
	$stmt = $db->prepare($argLine);
	$result = $stmt->execute($query_params); }
	catch (PDOException $ex) {
		header('HTTP/1.1 500 Internal Server Error');
		throw new RuntimeException('Failed to add file!');
		//trigger_error("Failed to add file!");
		die();
	}


//then start file check returning all critical and non-critical errors (for dev purposes, you should create a php file which doesn't add anything to the database, instead just returns
//a combination of errors).


	$full_file_path = 'server/php/files/' . $projectID . '/' . $file_name;

//check that the file exists 
	if (!file_exists($full_file_path))
	{
		header('HTTP/1.1 500 Internal Server Error');
		throw new RuntimeException("File not found on server!");
		die();
	}

//2d array with all file data will go here
	$file_array = array();

//store all error messages here
	$critical_errors = array();
	$noncritical_errors = array();

//open and read the file here
	try{
		switch($delimiter)
		{
			case "TAB": $file_array= array_map(function($v){return str_getcsv($v, "\t");}, file($full_file_path)); break;
			case "COMMA": $file_array=array_map(function($v){return str_getcsv($v, ",");}, file($full_file_path)); break;
			case "WHITESPACE": $file_array=array_map(function($v){return str_getcsv($v, " ");}, file($full_file_path)); break;
			case "SEMICOLON": $file_array=array_map(function($v){return str_getcsv($v, ";");}, file($full_file_path)); break;
		}
	}
	catch(RuntimeException $ex)
	{
		header('HTTP/1.1 500 Internal Server Error');
		throw new RuntimeException("File could not be read. Please resubmit.");
		die();
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

//Break out here if there are redundant headers or if any header cannot be found!
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
			is_numeric($file_array[$i][$quant_header_index]) ? $has_quant_val=true : null;
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

function CheckInputs ($identifier, $feature_descriptors, $quant, $log2_transform, $impute, $filter, $branch_id, $set_name, $delimiter, $projectID, $db)
{
	try{

		//Check that values have been provided for all POST variables
		if (empty($identifier) || empty($feature_descriptors) || empty($quant) || !is_numeric($log2_transform) || !is_numeric($impute) || empty($filter) || empty($branch_id) || empty($set_name) || empty($delimiter))
		{
			return false;
		}
		//
		//check that you have one identifier and that it contains a 'header' and a 'userName' element
		$identifier_validate_obj = json_decode($identifier, true);
		if (count($identifier_validate_obj)!==1)
		{
			return false;
		}
		if(!array_key_exists('header', $identifier_validate_obj[0]) || !array_key_exists('userName', $identifier_validate_obj[0]))
		{
			return false;
		}
		//check that each feature descriptor object contains a 'header' and 'userName' element
		$feature_descriptors_validate_array = json_decode($feature_descriptors, true);
		foreach($feature_descriptors_validate_array as $header)
		{
			if(!array_key_exists('header', $header) || !array_key_exists('userName', $header))
			{
				return false;
			}
		}
		//check that you have at least one quant header object and that each one contains a 'header', 'condName', 'repName', and 'control' element.
		$quant_validate_array = json_decode($quant, true);
		if(count($quant_validate_array)==0)
		{
			return false;
		}
		foreach($quant_validate_array as $header)
		{
			if (!array_key_exists('header', $header) || !array_key_exists('condName', $header) || !array_key_exists('repName', $header) || !array_key_exists('control', $header))
			{
				return false;
			}
		}
		//check that log2_transform and impute variables are both numeric
		if (!is_numeric($log2_transform) || !is_numeric($impute))
		{
			return false;
		}
		//check that delimiter represents one of the provided delimiters
		if ($delimiter!=="TAB" && $delimiter!=="COMMA" && $delimiter!=="SEMICOLON" && $delimiter!=="WHITESPACE")
		{
			return false;
		}
		//check that the filter object has a 'type' of 'TOTAL', 'COND', or 'NONE', and that 'p1' and 'p2' elements are provided
		$filter_validate_obj = json_decode($filter, true);
		if(!array_key_exists('type', $filter_validate_obj) || !array_key_exists('p1', $filter_validate_obj) || !array_key_exists('p2', $filter_validate_obj))
		{
			return false;
		}
		$matchingType = false;
		switch ($filter_validate_obj['type']) {
			case 'TOTAL': $matchingType=true; break;
			case 'COND': $matchingType=true; break;
			case 'NONE': $matchingType=true; break;
			default:break;
		}
		if(!$matchingType || !is_numeric($filter_validate_obj['p1']) || !is_numeric($filter_validate_obj['p2']))
		{
			return false;
		}

		//check that branch id exists
		$query = "SELECT * FROM project_branches WHERE project_id=:project_id AND branch_id=:branch_id";
		$query_params = array(':project_id' => $projectID, ':branch_id'=>$branch_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetch();
		if(!$row)
		{
			return false;
		}

		//check that set name DOES NOT exist
		$query = "SELECT * FROM project_sets WHERE project_id=:project_id AND set_name=:set_name";
		$query_params = array(':project_id' => $projectID, ':set_name'=>$set_name);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetch();
		if($row)
		{
			return false;
		}
		return true;
	}
	catch (RuntimeException $ex)
	{
		return false;
	}
}

	?>
