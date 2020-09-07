<?php
require("config.php");
require("pcaTest.php");

$projectID=-1;

while (true)
{
	//lock the process queue table
	LockTable($db);

	//query any waiting tasks
	//$query = "SELECT * FROM process_queue WHERE project_id=:project_id AND completed=0 AND running=0 ORDER BY task_creation_time LIMIT 1"; //Use this here when not doing dev work
	$query = "SELECT * FROM process_queue WHERE project_id=:project_id AND completed=0 ORDER BY task_creation_time LIMIT 1";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		//unlock the table and break out
		UnlockTable($db);
		break;
	}

	//if still here grab the relevant parameters
	$process_id=$row['process_id'];
	$set_id=$row['set_id'];
	$task = $row['task'];
	$branch_id=$row['branch_id'];


	//update the table and unlock
	UpdateProcessStatus($process_id, $db);
	UnlockTable($db);

	try{
		switch ($task) {
			case 'UPLOAD':
			HandleUpload($process_id, $set_id, $projectID, $db);
			break;

			case 'EDIT':
			HandleEdit($process_id, $branch_id, $projectID, $db);
			break;

			case 'CONTROL':
			HandleUpload($process_id, $set_id, $projectID, $db);
			break;

			case 'LOG2':
			HandleUpload($process_id, $set_id, $projectID, $db);
			break;

			case 'IMPUTE':
			HandleUpload($process_id, $set_id, $projectID, $db);
			break;

			case 'REPROCESS':
			HandleUpload($process_id, $set_id, $projectID, $db);
			break;

			case 'TYPE':
			HandleUpload($process_id, $set_id, $projectID, $db);
			break;
			
			case 'FILTER':
			HandleUpload($process_id, $set_id, $projectID, $db);
			break;

			default:
				# code...
			break;
		}
	}
	catch (Exception $e)
	{
		
	}

	//Go through this routine when you finish a process before checking for the next.
	LockTable($db);
	FinishProcess($process_id, $db);
	UnlockTable($db);

	//keep this on for dev, you don't want to get into endless loops...
	//break;
}

function HandleEdit($process_id, $branch_id, $projectID, $db)
{
	//Check that there is work to be done on this branch
	$work_to_do = CheckForExistingWork($branch_id, $projectID, $db);
	if(!$work_to_do)
	{
		return;
	}

	//get master id list
	$master_id_list = GetCurrentIdentifiers($projectID, $db); UpdateProgress($process_id, ((1/6)*100), $db);

	//DO OUTLIER ANALYSIS -- WRITE TO DATABASE IMMEDIATELY AFTERWARDS AND UNSET RESULTS
	$outlier_data = DoOutlierAnalysis($branch_id, $master_id_list, $db);
	AddOutlierDataToDatabase($outlier_data, $projectID, $branch_id, $db); UpdateProgress($process_id, ((2/6)*100), $db);
	$outlier_data = null;

	//UPDATE PROCESS DATA SUMMARY
	UpdateProjectDataSummary($branch_id, $projectID, $db); UpdateProgress($process_id, ((3/6)*100), $db);

	//GET ALL DATA FOR PCA
	$pca_cond_dict = GetPCAQuantDict($db, $projectID, $branch_id); UpdateProgress($process_id, ((4/6)*100), $db);

	//DO PCA ANALYSIS -- WRITE TO DATABASE IMMEDIATELY AFTERWARDS AND UNSET RESULTS
	$cond_pca_results = DoPCA($pca_cond_dict, $master_id_list, false);
	AddPCADataToDatabase($cond_pca_results, $db, $projectID, $set_id, $branch_id, true); UpdateProgress($process_id, ((5/6)*100), $db);
	$cond_pca_results = null;

	$pca_cond_dict = GetPCAQuantDict($db, $projectID, $branch_id);
	$rep_pca_results = DoPCA($pca_cond_dict, $master_id_list, true);
	AddPCADataToDatabase($rep_pca_results, $db, $projectID, $set_id, $branch_id, false); UpdateProgress($process_id, ((6/6)*100), $db);
	$rep_pca_results = null;

}

function HandleUpload($process_id, $set_id, $projectID, $db)
{
	$time_pre = microtime(true);
	$file_array = GetFileInformation($set_id, $projectID, $process_id, $db);
	if (empty($file_array))
	{
		return;
	}
	$og_file_name=$file_array['og_file_name'];
	$file_name=$file_array['file_name'];
	$identifier = $file_array['identifier'];
	$feature_descriptors = $file_array['feature_descriptors'];
	$quant = $file_array['quant'];
	$log2_transform = $file_array['log2_transform'];
	$impute = $file_array['impute'];
	$filter = $file_array['filter'];
	$branch_id= $file_array['branch_id'];
	$set_name = $file_array['set_name'];
	$delimiter = $file_array['delimiter'];
	$file_id = $file_array['file_id'];
	$organism_id=$file_array['organism_id'];
	$standard_id_column=$file_array['standard_id_column'];
	$standard_id_type=$file_array['standard_id_type'];

	//you've got all of the pieces here, you should be ready to go.
	$full_file_path = '../../server/php/files/' . $projectID . '/' . $file_name;
	$truncated_file_path = str_replace(".txt", '_' . $set_id . ".txt", $file_name);
	//$file_array = ReadFileToArray($full_file_path, $delimiter);
	$file_array = ReadFileToArrayByLine($full_file_path, $delimiter, $quant, $identifier, $feature_descriptors);
	$quant_header_indexes = GetHeaderIndexes($file_array, $quant);
	$feature_descriptor_header_indexes = GetHeaderIndexes($file_array, $feature_descriptors);
	$identifier_indexes = GetHeaderIndexes($file_array, $identifier);
	ZeroOutNumericData($file_array, $quant_header_indexes);
	$condition_header_indexes = GetConditionHeaderArrays($file_array, $quant, $quant_header_indexes);

	/*TruncateFileArray($file_array, $quant_header_indexes, $feature_descriptor_header_indexes, $identifier_indexes);

	$quant_header_indexes = GetHeaderIndexes($file_array, $quant);
	$feature_descriptor_header_indexes = GetHeaderIndexes($file_array, $feature_descriptors);
	$identifier_indexes = GetHeaderIndexes($file_array, $identifier);*/

	ApplyFilter($file_array, $filter, $quant_header_indexes, $quant, $condition_header_indexes);

	//////////////THIS IS TEMPORARY AND NEEDS TO BE DELETED///////////////
	//DeleteForDev($db, $projectID);

	//MAKE ALL IDENTIFIERS UNIQUE HERE. (1/18)
	UniqueifyIdentifiers($file_array, $identifier_indexes, $identifier); UpdateProgress($process_id, (1/18)*100, $db);

	//GET CURRENT IDENTIFIERS FROM DATABASE, MERGE EXISTING AND NEW IDENTIFIERS, ADD NEW IDENTIFIERS TO DATABASE (2/18) (Don't worry about locking since you are only doing one write process/project at a time)
	$master_id_list = UpdateIdentifiersInDatabase($file_array, $identifier_indexes, $identifier, $process_id, $db, $file_id, $projectID); UpdateProgress($process_id, (2/18)*100, $db);

	//MAP CURRENT IDENTIFIERS TO STANDARD MOLECULE IDENTIFIERS FOR ORGANISM-SPECIFIC ANALYSES
	$standard_id_mappings = GetStandardIDMappings($master_id_list, $organism_id, $standard_id_column, $standard_id_type, $db);

	//CREATE REPLICATE OBJECTS WTH THE APPROPRIATE ARRAYS (3/18)
	$replicate_dict = CreateReplicateObjects($file_array, $identifier_indexes, $master_id_list, $projectID, $db, $file_id, $quant, $quant_header_indexes, $log2_transform); UpdateProgress($process_id, (3/18)*100, $db);

	//CREATE CONDITION OBJECTS WITH THE APPROPRIATE ARRAYS (4/18)
	$condition_dict = CreateConditionObjects($replicate_dict, $quant, $quant_header_indexes, $set_name); UpdateProgress($process_id, (4/18)*100, $db);

	//GET CONDITION AND SET NAMES --> MAP IDENTIFIERS TO OBJECTS (5/18)
	MapDatabaseIdentifiersToCondRep($condition_dict, $projectID, $db, $file_id); UpdateProgress($process_id, (5/18)*100, $db);

	//IMPUTE MISSING VALUES IF NECESSARYs (6/18)
	$imputation_settings = ImputeMissingValues($replicate_dict, $condition_dict, $impute, $log2_transform); UpdateProgress($process_id, (6/18)*100, $db);

	//ADD IMPUTATION SETTINGS TO THE DATABASE (7/18)
	AddImputationSettingsToDatabase($imputation_settings, $projectID, $branch_id, $set_id, $db); UpdateProgress($process_id, (7/18)*100, $db);

	//CALCULATE MEAN AND CONTROL NORMALIZED VALUES AND P-VALUES (8/18)
	CalculateConditionDescriptiveStatistics($condition_dict, $log2_transform); UpdateProgress($process_id, (8/18)*100, $db);

	//WRITE A TRUNCATED FILE WITH ALL IMPUTED VALUES AND RELEVANT COLUMNS WITHIN (9/18)
	WriteTruncatedFile($file_array, $identifier_indexes, $feature_descriptor_header_indexes, $quant_header_indexes, $quant, $feature_descriptors, $identifier, $truncated_file_path, $file_id, $db, $projectID, $condition_dict, $master_id_list); UpdateProgress($process_id, (9/18)*100, $db);

	//ADD ALL DATA TO THE DATABASE

	//ADD REPLICATES TO DATABASE (10/18)
	AddReplicateDataToDatabase($condition_dict, $projectID, $set_id, $branch_id, $file_id, $db, $standard_id_mappings); UpdateProgress($process_id, (10/18)*100, $db);

	//PERFORM MULTIPLE TESTING CORRECTION HERE (11/18)
	DoMultipleTestingCorrection($condition_dict); UpdateProgress($process_id, (11/18)*100, $db);

	//ADD CONDITION DATA TO DATABASE (12/18)
	AddConditionDataToDatabase($condition_dict, $projectID, $set_id, $branch_id, $file_id, $db, $standard_id_mappings);
	AddDataDescriptiveStatisticsToDatabase($condition_dict, $projectID, $set_id, $branch_id, $file_id, $db, $standard_id_mappings); UpdateProgress($process_id, (12/18)*100, $db);

	//UPDATE QUERY TERMS IN THE DATABASE (13/18)
	UpdateQueryTerms($condition_dict, $master_id_list, $db, $projectID, $set_name, $branch_id, $set_id); UpdateProgress($process_id, (13/18)*100, $db);

	$condition_dict = null;
	$replicate_dict = null;

	//ADD NEW FEATURE METADATA TO THE DATABASE (14/18)
	AddFeatureMetaDataToDatabase($file_array, $master_id_list, $db, $projectID, $identifier_indexes, $feature_descriptor_header_indexes, $feature_descriptors); UpdateProgress($process_id, (14/18)*100, $db);

	$file_array = null;

	//DO OUTLIER ANALYSIS -- WRITE TO DATABASE IMMEDIATELY AFTERWARDS AND UNSET RESULTS (15/18)
	$outlier_data = DoOutlierAnalysis($branch_id, $master_id_list, $db);
	AddOutlierDataToDatabase($outlier_data, $projectID, $branch_id, $db); UpdateProgress($process_id, (15/18)*100, $db);
	$outlier_data = null;

	//UPDATE PROCESS DATA SUMMARY (16/18)
	UpdateProjectDataSummary($branch_id, $projectID, $db); UpdateProgress($process_id, (16/18)*100, $db);

	//GET ALL DATA FOR PCA
	$pca_cond_dict = GetPCAQuantDict($db, $projectID, $branch_id);

	//DO PCA ANALYSIS -- WRITE TO DATABASE IMMEDIATELY AFTERWARDS AND UNSET RESULTS (17/18) & (18/18)*100
	$cond_pca_results = DoPCA($pca_cond_dict, $master_id_list, false);
	AddPCADataToDatabase($cond_pca_results, $db, $projectID, $set_id, $branch_id, true); UpdateProgress($process_id, (17/18)*100, $db);
	$cond_pca_results = null;

	$pca_cond_dict = GetPCAQuantDict($db, $projectID, $branch_id);
	$rep_pca_results = DoPCA($pca_cond_dict, $master_id_list, true);
	AddPCADataToDatabase($rep_pca_results, $db, $projectID, $set_id, $branch_id, false); UpdateProgress($process_id, (18/18)*100, $db);
	$rep_pca_results = null;

}

//General Methods
function GetFileInformation($set_id, $projectID, $process_id, $db) //Gets all file information and returns in an array
{
	$query = "SELECT * FROM project_files WHERE set_id=:set_id AND project_id=:project_id LIMIT 1";
	$query_params = array(':set_id' => $set_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		LockTable($db);
		FinishProcess($process_id, $db);
		UnlockTable($db);
		return null;
	}
	$og_file_name=$row['original_file_name'];
	$file_name=$row['file_name'];
	$identifier = $row['identifier'];
	$feature_descriptors = $row['feature_descriptors'];
	$quant = $row['quant'];
	$log2_transform = $row['log2_transform'];
	$impute = $row['impute_missing_values'];
	$filter = $row['filter'];
	$branch_id= $row['branch_id'];
	$set_name = $row['set_name'];
	$delimiter = $row['delimiter'];
	$file_id=$row['file_id'];
	$organism_id=$row['organism_id'];
	$standard_id_column=$row['standard_id_column'];
	$standard_id_type=$row['standard_id_type'];
	$return_array = array("og_file_name" => $og_file_name, "file_name" => $file_name, "identifier" => $identifier, "feature_descriptors" => $feature_descriptors,
		"quant" => $quant, "log2_transform" => $log2_transform, "impute" => $impute, "filter" => $filter, "branch_id" => $branch_id,
		"set_name" => $set_name, "delimiter" => $delimiter, "file_id" => $file_id, "organism_id" => $organism_id, "standard_id_column" => $standard_id_column, "standard_id_type"=> $standard_id_type);
	return $return_array;
}
function ReadFileToArray($full_file_path, $delimiter) //Reads a text file into a 2D array
{
	$file_array = array();
	try{
		switch($delimiter)
		{
			case "TAB": $file_array= array_map(function($v){return str_getcsv($v, "\t");}, file($full_file_path)); break;
			case "COMMA": $file_array=array_map(function($v){return str_getcsv($v, ",");}, file($full_file_path)); break;
			case "WHITESPACE": $file_array=array_map(function($v){return str_getcsv($v, " ");}, file($full_file_path)); break;
			case "SEMICOLON": $file_array=array_map(function($v){return str_getcsv($v, ";");}, file($full_file_path)); break;
		}
		return $file_array;
	}
	catch(RuntimeException $ex)
	{
		LockTable($db);
		FinishProcess($process_id, $db);
		UnlockTable($db);
		return null;
	}
}
function ReadFileToArrayByLine($full_file_path, $delimiter, $quant, $identifier, $feature_descriptors)
{
	$header_array = array();
	$file_array = array();
	$quant_obj_array = json_decode($quant, true);
	$feature_descriptors_obj = json_decode($feature_descriptors, true);
	$identifier_obj = json_decode($identifier, true);
	foreach ($quant_obj_array as $entry) {
		$header_array[$entry['header']]="";
	}
	foreach ($feature_descriptors_obj as $entry) {
		$header_array[$entry['header']]="";
	}
	foreach ($identifier_obj as $entry) {
		$header_array[$entry['header']]="";
	}
	$file = fopen($full_file_path, "r");
	$header_line = fgets($file);
	$header_line = str_replace("\r\n", '', $header_line);
	$header_parts = array();

	switch ($delimiter) {
			case "TAB": $header_parts=explode("\t", $header_line); break;
			case "COMMA": $header_parts=explode(",", $header_line); break;
			case "WHITESPACE": $header_parts=explode(" ", $header_line); break;
			case "SEMICOLON": $header_parts=explode(";", $header_line); break;
		default:
			# code...
			break;
	}

	$tmp_array = array();
	$index_array = array();
	for ($i = 0; $i < count($header_parts); $i++)
	{
		$curr_header = $header_parts[$i];
		if (array_key_exists($curr_header, $header_array))
		{
			array_push($tmp_array, $curr_header); array_push($index_array, $i);
		} 
	}
	array_push($file_array, $tmp_array);

	$max_index = max($index_array);

	while (!feof($file))
	{
		$line = fgets($file);
		$line = str_replace("\r\n", '', $line);
		$line_parts = array();
		switch ($delimiter) {
			case "TAB": $line_parts=explode("\t", $line); break;
			case "COMMA": $line_parts=explode(",", $line); break;
			case "WHITESPACE": $line_parts=explode(" ", $line); break;
			case "SEMICOLON": $line_parts=explode(";", $line); break;
		default:
			# code...
			break;
		}
		$tmp_array = array();
		if(count($line_parts)>$max_index)
		{
			foreach ($index_array as $index) {
				array_push($tmp_array, $line_parts[$index]);
			}
			array_push($file_array, $tmp_array);
		}
	}
	fclose($file);
	return $file_array;
}
function GetHeaderIndexes($file_array, $array) //Creates array with header->index key-value pairs
{
	$return_array = array();
	$quant_header_array = json_decode($array, true);
	foreach ($quant_header_array as $header) {
		$curr_header = $header['header'];
		$return_array[$curr_header] = 0;
	}
	for ($i = 0; $i < count($file_array[0]); $i++)
	{
		$curr_header = $file_array[0][$i];
		if (array_key_exists($curr_header, $return_array))
		{
			$return_array[$curr_header] = $i;
		}
	}
	return $return_array;
}
function GetConditionHeaderArrays($file_array, $quant, $quant_header_indexes)
{
	$quant_obj = json_decode($quant, true);
	$return_array = array();
	foreach ($quant_obj as $obj) {
		if (empty($return_array[$obj['condName']]))
		{
			$return_array[$obj['condName']] = array();
		}
		array_push($return_array[$obj['condName']], $quant_header_indexes[$obj['header']]);
	}
	return $return_array;
}
function ZeroOutNumericData(&$file_array, $quant_header_indexes) //Converts all null, empty, negative, and non-numeric quant values to 0 (& allows this)
{
	for ($i = 1; $i < count($file_array); $i++)
	{
		foreach ($quant_header_indexes as $index) {
			if (!is_numeric($file_array[$i][$index]))
			{
				$file_array[$i][$index] = 0;
			}
			else
			{
				if ($file_array[$i][$index]<0)
				{
					$file_array[$i][$index] = 0;
				}
			}
		}
	}
}
function ApplyFilter(&$file_array, $filter, $quant_header_indexes, $quant, $condition_header_indexes) //Parses filter parameters and applies the filter to $file_array
{
	$filter_obj = json_decode($filter, true);
	$type = $filter_obj['type'];
	$p1 = $filter_obj['p1'];
	$p2 = $filter_obj['p2'];
	switch ($type) {
		case 'NONE':
		return;
		break;
		case 'TOTAL': //must be seen in at least $p1 % of replicates
		$return_array = array();
		array_push($return_array, $file_array[0]);
		for ($i = 1; $i < count($file_array); $i++)
		{
			$curr_count = 0;
			foreach ($quant_header_indexes as $index) {
				$file_array[$i][$index]>0?$curr_count++ : null;
			}
			$curr_percent = ((double)$curr_count)/((double)count($quant_header_indexes));
			$curr_percent *= 100;
			$curr_percent >= $p1 ? array_push($return_array, $file_array[$i]) : null;
		}
		$file_array = $return_array;
		break;
		case 'COND': //must be seen in at least p1 % of replicates in at least p2 conditions
		$return_array = array();
		array_push($return_array, $file_array[0]);
		for ($i = 1; $i < count($file_array); $i++)
		{
			$curr_count = 0;
			foreach ($condition_header_indexes as $obj) {
				$curr_cond_count = 0;
				foreach ($obj as $key => $index) {
					$file_array[$i][$index] > 0 ? $curr_cond_count++ : null;
				}
				$curr_percent = ((double)$curr_cond_count)/((double)count($obj));
				$curr_percent *= 100;
				$curr_percent >= $p1 ? $curr_count++ : null;
			}
			$curr_count >= $p2 ? array_push($return_array, $file_array[$i]) : null;
		}
		$file_array = $return_array;
		break;
		default:
			# code...
		break;
	}
}
function TruncateFileArray(&$file_array, $quant_header_indexes, $feature_descriptor_header_indexes, $identifier_indexes)
{
	$header_array = array();
	foreach ($quant_header_indexes as $key => $value) {
		$header_array[$key] = "";
	}
	foreach ($feature_descriptor_header_indexes as $key => $value) {
		$header_array[$key] = "";
	}
	foreach ($identifier_indexes as $key => $value) {
		$header_array[$key] = "";
	}

	TransposeArray($file_array);
	$delete_indices = array();

	$count = count($file_array);
	for ($i = 0; $i < count($file_array); $i++)
	{
		$curr_header = $file_array[$i][0];
		if (!array_key_exists($curr_header, $header_array))
		{
			array_push($delete_indices, $i);
		}
	}

	$delete_indices = array_reverse($delete_indices);

	foreach ($delete_indices as $idx) {
		unset($file_array[$idx]);
	}
	$file_array = array_values($file_array);

	TransposeArray($file_array);
}
function UniqueifyIdentifiers(&$file_array, $identifier_indexes, $identifier) //Reads all identifiers in the ID column and appends with '-#' variant to ensure uniqueness.
{
	$identifier_obj = json_decode($identifier, true);
	$index = 0;
	foreach ($identifier_indexes as $id_index) {
		$index = $id_index;
	}
	$usage_array = array();
	for ($i = 1; $i < count($file_array); $i++)
	{
		$curr_id = $file_array[$i][$index];
		empty($usage_array[$curr_id]) ? $usage_array[$curr_id] = 1 : $usage_array[$curr_id]++;
	}
	$usage_array_2 = array();
	for ($i = 1; $i < count($file_array); $i++)
	{
		$curr_id = $file_array[$i][$index];
		if($usage_array[$curr_id] > 1)
		{
			empty($usage_array_2[$curr_id]) ? $usage_array_2[$curr_id] = 0 : null;
			$usage_array_2[$curr_id]++;
			$curr_id .= "-" . $usage_array_2[$curr_id];
			$file_array[$i][$index] = $curr_id;
		} 
	}
}
function GetCurrentIdentifiers($projectID, $db) //Gets all current project molecule ids [id name=>identifier]
{
	$query = "SELECT * FROM data_unique_identifiers WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	if(!$row)
	{
		return null;
	}
	$return_array = array();
	foreach($row as $entry)
	{
		$return_array[$entry['unique_identifier_text']] = $entry['unique_identifier_id'];
	}
	return $return_array;
}
function GetMaxIdentifierNumber($projectID, $db)
{
	$query = "SELECT MAX(unique_identifier_number) FROM data_unique_identifiers WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$max_identifier_number = 1;
	if($row)
	{
		is_numeric($row['MAX(unique_identifier_number)']) ? $max_identifier_number = $row['MAX(unique_identifier_number)']+1: null;
	}
	return $max_identifier_number;
}
function AddIdentifiersToDatabase($projectID, $db, $identifiers_to_push)
{
	$row_length = count($identifiers_to_push[0]);
	$nb_rows = count($identifiers_to_push);
	$length = $row_length * $nb_rows;
	$args = implode(',', array_map(
		function($el) { return '('.implode(',', $el).')'; },
		array_chunk(array_fill(0, $length, '?'), $row_length)
		));
	$query_params = array();
	foreach($identifiers_to_push as $array)
	{
		foreach($array as $value)
		{
			$query_params[] = $value;
		}
	}
	$insertText = "INSERT INTO data_unique_identifiers (project_id, unique_identifier_text, unique_identifier_id, unique_identifier_number, original_file_id) VALUES " . $args;
	try{
		$stmt = $db->prepare($insertText);
		$result = $stmt->execute($query_params);
	}
	catch (PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
}
function UpdateIdentifiersInDatabase($file_array, $identifier_indexes, $identifier, $process_id, $db, $file_id, $projectID)
{
	$identifier_obj = json_decode($identifier, true);
	$index = 0;
	foreach ($identifier_indexes as $id_index) {
		$index = $id_index;
	}
	$all_identifier_list = array();
	for ($i=1; $i < count($file_array); $i++)
	{
		array_push($all_identifier_list, $file_array[$i][$index]);
	}
	$current_identifiers = GetCurrentIdentifiers($projectID, $db);
	$max_identifier_number = GetMaxIdentifierNumber($projectID, $db);
	$master_id_list = array();
	$identifiers_to_push = array();
	foreach ($all_identifier_list as $curr_id) {
		if (empty($current_identifiers[$curr_id]))
		{
			array_push($identifiers_to_push, array($projectID, $curr_id, $projectID . "-" . $max_identifier_number, $max_identifier_number, $file_id));
			$master_id_list[$curr_id] =  $projectID . "-" . $max_identifier_number;
			$max_identifier_number++;
		}
		else
		{
			$master_id_list[$curr_id] = $current_identifiers[$curr_id];
		}
	}
	count($identifiers_to_push) > 0 ? AddIdentifiersToDatabase($projectID, $db, $identifiers_to_push) : null;
	return $master_id_list;
}
function GetStandardIDMappings($master_id_list, $organism_id, $standard_id_column, $standard_id_type, $db)
{
	//if organism id === -1 or $standard_id_column is empty or $standard_id_type===-1 return right away
	$standard_id_mapping_array = array();
	if ($organism_id===-1 || empty($standard_id_column) || $standard_id_type===-1)
	{
		foreach ($master_id_list as $key => $value) {
			$standard_id_mapping_array[$value] = -1;
		}
		return $standard_id_mapping_array;
	}
	$query = "SELECT molecule_id, id_text FROM standard_molecules WHERE id_type=:id_type AND organism_id=:organism_id";
	$query_params = array(':id_type'=> $standard_id_type, ':organism_id' => $organism_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	$id_text_to_id_dict = array();
	foreach ($row as $entry) {
	 	$id_text_to_id_dict[$entry['id_text']] = $entry['molecule_id'];
	}

	foreach ($master_id_list as $key => $value) {
		$standard_id_mapping_array[$value] = -1;
		$pattern = '/[;,:|]/';
		$key_list = preg_split( $pattern, $key);
		foreach ($key_list as $split_key) {
			array_key_exists($split_key, $id_text_to_id_dict) ? $standard_id_mapping_array[$value] = $id_text_to_id_dict[$split_key] : null;
		}		
	}
	return $standard_id_mapping_array;
	
}
function CreateReplicateObjects($file_array, $identifier_indexes, $master_id_list, $projectID, $db, $file_id, $quant, $quant_header_indexes, $log2_transform)
{
	//decode quant obj
	$quant_obj_array = json_decode($quant, true);

	$replicate_dict = array();

	//iterate all and create replicate objects
	foreach ($quant_obj_array as $quant_obj) {
		$replicate_obj = new Replicate();
		$replicate_obj->header_text=$quant_obj['header'];
		$replicate_obj->condition_name=$quant_obj['condName'];
		$replicate_obj->replicate_name=$quant_obj['repName'];
		$replicate_obj->is_control = $quant_obj['control']==="Yes"? 1 : 0;
		$replicate_obj->project_id=$projectID;
		$replicate_obj->file_id=$file_id;
		$replicate_dict[$quant_obj['header']] = $replicate_obj;
	}

	$identifier_index = 0;
	foreach ($identifier_indexes as $index) {
		$identifier_index = $index;
	}
	
	for ($i = 1; $i<count($file_array); $i++)
	{
		$curr_id = $master_id_list[$file_array[$i][$identifier_index]];
		foreach ($replicate_dict as $rep) {
			$quant_val = $file_array[$i][$quant_header_indexes[$rep->header_text]];
			$adj_quant_val = ($log2_transform==="1" && $quant_val!=="0")? log($quant_val, 2) : $quant_val;
			$adj_quant_val = $adj_quant_val==="0"? 0:$adj_quant_val;
			$rep->quant_dict_pre_imputation[$curr_id] = $adj_quant_val;
		}
	}
	return $replicate_dict;
}
function CreateConditionObjects($replicate_dict, $quant, $quant_header_indexes, $set_name)
{
	//decode quant obj
	$quant_obj_array = json_decode($quant, true);

	$condition_dict = array();

	foreach ($replicate_dict as $rep) {
		if (empty($condition_dict[$rep->condition_name]))
		{
			$condition_obj = new Condition();
			$condition_obj->condition_name=$rep->condition_name;
			$condition_obj->is_control=$rep->is_control;
			$condition_obj->file_id=$rep->file_id;
			$condition_obj->project_id=$rep->project_id;
			$condition_dict[$rep->condition_name] = $condition_obj; 
		}
		$condition_dict[$rep->condition_name]->replicate_list[$rep->header_text] = $rep;
	}
	return $condition_dict;
}
function ImputeMissingValues(&$replicate_dict, &$condition_dict, $impute, $log2_transform)
{
	$impute_settings = array();

	if($impute==="0")
	{
		foreach ($condition_dict as $cond) {
			foreach ($cond->replicate_list as $rep) {
				foreach ($rep->quant_dict_pre_imputation as $key => $value) {
					if ($value != 0)
					{
						$rep->quant_dict[$key] = $value;
					}
				}
				$setting_array = array('replicate_id' => $rep->replicate_id, 'condition_id' => $cond->condition_id, 'imputation_threshold' => -1, 'imputation_average' => -1, 'imputation_std_dev'=>-1);
				array_push($impute_settings, $setting_array);
			}
		}
		return $impute_settings;
	}

	foreach ($condition_dict as $cond) {
		if (count($cond->replicate_list > 1))
		{
			$minCV = INF;
			$minIndex = -1;
			$complete_quant_arrays = array();
			$quantifiedIDList = array();
			foreach ($cond->replicate_list as $rep) {
				$complete_quant_arrays[$rep->header_text] = array();
				foreach (array_keys($rep->quant_dict_pre_imputation) as $quant_val_key) {
					$quant_val = $rep->quant_dict_pre_imputation[$quant_val_key];
					$quant_val > 0 ? array_push($complete_quant_arrays[$rep->header_text], $quant_val) AND $quantifiedIDList[$quant_val_key]="" : null;
				}
				sort($complete_quant_arrays[$rep->header_text]);
			}

			for ($i = 0; $i<=100; $i++)
			{
				$cond_rep_arrays = array();
				foreach ($cond->replicate_list as $rep) {
					$avg = $complete_quant_arrays[$rep->header_text][0];
					$sd = 0;
					$rep->quant_dict = $rep->quant_dict_pre_imputation;
					if ($i>0)
					{
						$threshold = (int)((count($complete_quant_arrays[$rep->header_text])) * ($i/100));
						$subset = array_slice($complete_quant_arrays[$rep->header_text], 0, $threshold);
						$avg = array_sum($subset)/count($subset);
						$sd = stats_standard_deviation($subset, true);
					}
					foreach (array_keys($rep->quant_dict) as $key) {
						$curr_val = $rep->quant_dict[$key];
						if ($curr_val===0)
						{
							$new_val =  stats_rand_gen_normal($avg, $sd);
							$rep->quant_dict[$key] = $new_val;
						}
						if(!array_key_exists($key, $cond_rep_arrays))
						{
							$cond_rep_arrays[$key] = array();
						}
						array_push($cond_rep_arrays[$key], $rep->quant_dict[$key]);
					}
				}
				//calculate cv here
				$calculated_cv_list = array();
				foreach (array_keys($cond_rep_arrays) as $key) {
					array_push($calculated_cv_list, CoefficientOfVariation($cond_rep_arrays[$key], $log2_transform==="1"? true : false));
				}
				$curr_cv = Mean($calculated_cv_list);
				$curr_cv < $minCV ? $minCV=$curr_cv AND $minIndex = $i : null;
			}
			
			//actually impute here
			foreach ($cond->replicate_list as $rep) {
				$avg = $complete_quant_arrays[$rep->header_text][0];
				$sd = 0;
				$rep->quant_dict = $rep->quant_dict_pre_imputation;
				$setting_array = array('replicate_id' => $rep->replicate_id, 'condition_id' => $cond->condition_id, 'imputation_threshold' => 0, 'imputation_average' => $avg, 'imputation_std_dev'=>$sd);
				if($minIndex>0)
				{
					$setting_array['imputation_threshold'] = $minIndex;
					$threshold = (int)((count($complete_quant_arrays[$rep->header_text])) * ($minIndex/100));
					$subset = array_slice($complete_quant_arrays[$rep->header_text], 0, $threshold);
					$avg = array_sum($subset)/count($subset);
					$sd = stats_standard_deviation($subset, true);
					$setting_array['imputation_average'] = $avg;
					$setting_array['imputation_std_dev'] = $sd;
					array_push($impute_settings, $setting_array);
				}
				foreach (array_keys($rep->quant_dict) as $key) {
					$curr_val = $rep->quant_dict[$key];
					if ($curr_val===0)
					{
						$new_val =  stats_rand_gen_normal($avg, $sd);
						$rep->quant_dict[$key] = $new_val;
					}
				}
			}
		}
	}
	return $impute_settings;
}
function AddImputationSettingsToDatabase($imputation_settings, $projectID, $branch_id, $set_id, $db)
{
	if (count($imputation_settings)===0)
	{
		return;
	}
	$insertArray = array();
	foreach ($imputation_settings as $entry) {
		array_push($insertArray, array($projectID, $branch_id, $set_id, $entry['condition_id'], $entry['replicate_id'], $entry['imputation_threshold'], $entry['imputation_average'], $entry['imputation_std_dev']));
	}
	$chunked_array = array_chunk($insertArray, 2000);

	foreach ($chunked_array as $chunk) {
		$row_length = count($chunk[0]);
		$nb_rows = count($chunk);
		$length = $row_length * $nb_rows;
		$args = implode(',', array_map(
			function($el) { return '('.implode(',', $el).')'; },
			array_chunk(array_fill(0, $length, '?'), $row_length)
			));

		$query_params = array();
		foreach($chunk as $array)
		{
			foreach($array as $value)
			{
				$query_params[] = $value;
			}
		}
		$insertText = "INSERT INTO project_imputation_settings (project_id, branch_id, set_id, condition_id, replicate_id, imputation_threshold, imputation_average, imputation_std_dev) VALUES " . $args;
		try{
			$stmt = $db->prepare($insertText);
			$result = $stmt->execute($query_params);
		}
		catch (PDOException $ex) {
			//die("Failed to run query: " . $ex->getMessage());
		}
	}
}
function MapDatabaseIdentifiersToCondRep(&$condition_dict, $projectID, $db, $file_id)
{
	$query = "SELECT a.replicate_id, a.header_text, a.condition_id FROM project_replicates AS a WHERE project_id=:project_id AND file_id=:file_id";
	$query_params = array(':project_id'=> $projectID, ':file_id' => $file_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	if(!$row)
	{
		return null;
	}

	foreach ($row as $curr_row) {
		foreach ($condition_dict as $cond) {
			if(array_key_exists($curr_row['header_text'], $cond->replicate_list))
			{
				$rep = $cond->replicate_list[$curr_row['header_text']];
				$rep->replicate_id = $curr_row['replicate_id'];
				$cond->condition_id = $curr_row['condition_id'];
				break;
			}
		}
	}
}
function CalculateConditionDescriptiveStatistics(&$condition_dict, $log2_transform)
{
	foreach ($condition_dict as $cond) {
		$complete_quant_arrays = array();
		foreach ($cond->replicate_list as $rep) {
			foreach ($rep->quant_dict as $key => $value) {
				if ($value != 0)
				{
					(!array_key_exists($key, $cond->quant_dict_all_vals)) ? $cond->quant_dict_all_vals[$key] = array() : null;
					(!array_key_exists($key, $complete_quant_arrays)) ? $complete_quant_arrays[$key] = array() : null;
					array_push($cond->quant_dict_all_vals[$key], $value);
				}
			}
		}
		foreach ($cond->quant_dict_all_vals as $key => $value) {
			$cond->quant_dict_avg_val[$key] = Mean($value);
			if (count($value)>1)
			{
				$cond->quant_dict_cvs[$key] = CoefficientOfVariation($value, true);
				$cond->quant_dict_sds[$key] = stats_standard_deviation($value, true);
			}
			else
			{
				$cond->quant_dict_cvs[$key] = 0;
				$cond->quant_dict_sds[$key] = 0;
				//count($value)===1 ? $cond->quant_dict_cvs[$key] = 0 AND $cond->quant_dict_sds[$key] = 0 : null;
			}
		}
	}

	$control_condition = null;
	$control_condition_id = "-1";
	foreach ($condition_dict as $cond) {
		$cond->is_control===1 ? $control_condition = $cond AND $control_condition_id=$cond->condition_id: null;
	}

	if (!empty($control_condition))
	{
		$complete_control_array = array();
		foreach ($cond->replicate_list as $rep) {
			foreach ($rep->quant_dict as $key => $value) {
				if ($value!==0)
				{
					if(empty($complete_control_array[$key]))
					{
						$complete_control_array[$key] = array();
					}
					array_push($complete_control_array[$key], $value);
				}
			}
		}
	}

	foreach ($condition_dict as $cond) {
		if ($cond->condition_id===$control_condition_id)
		{
			continue;
		}
		foreach (array_keys($cond->quant_dict_all_vals) as $key) {
			if (empty($cond->quant_dict_all_vals[$key]))
			{
				continue;
			}
			if (count($cond->quant_dict_all_vals[$key])===0)
			{
				continue;
			}
			$complete_quant_array = array();
			$complete_quant_p_value_array = array();
			$complete_quant_array = array_merge($complete_quant_array, $cond->quant_dict_all_vals[$key]);

			foreach ($condition_dict as $otherCond) {
				if ($cond->condition_id!==$otherCond->condition_id)
				{
					if (array_key_exists($key, $otherCond->quant_dict_all_vals))
					{
						$complete_quant_array = array_merge($complete_quant_array, $otherCond->quant_dict_all_vals[$key]);
						$complete_quant_p_value_array = array_merge($complete_quant_p_value_array, $otherCond->quant_dict_all_vals[$key]);
					}
				}
			}

			if (count($complete_quant_p_value_array)>0 && count($cond->quant_dict_all_vals[$key])>0)
			{
				$mean_norm_val = $cond->quant_dict_avg_val[$key] - Mean($complete_quant_p_value_array);
				$mean_norm_p_val = 1; 
				count($cond->quant_dict_all_vals[$key]) >1 && count($complete_quant_p_value_array) > 1 ? $mean_norm_p_val = tTestTwoSample($cond->quant_dict_all_vals[$key], $complete_quant_p_value_array) : null;
				//$mean_norm_p_val = tTestTwoSample($cond->quant_dict_all_vals[$key], $complete_quant_p_value_array);
				$cond->quant_dict_mean_normalized[$key] = $mean_norm_val;
				$cond->quant_dict_mean_normalized_p_value[$key] = $mean_norm_p_val;
				if (!empty($control_condition))
				{
					if (array_key_exists($key, $control_condition->quant_dict_all_vals))
					{
						$control_norm_val = $cond->quant_dict_avg_val[$key] - Mean($control_condition->quant_dict_all_vals[$key]);
						$control_norm_p_value = 1;
						count($cond->quant_dict_all_vals[$key])>1 && count($control_condition->quant_dict_all_vals[$key])>1 ? $control_norm_p_value = tTestTwoSample($cond->quant_dict_all_vals[$key], $control_condition->quant_dict_all_vals[$key]) : null;
						$cond->quant_dict_control_normalized[$key] = $control_norm_val;
						$cond->quant_dict_control_normalized_p_value[$key] = $control_norm_p_value;
					}
				}
				else
				{
					$cond->quant_dict_control_normalized[$key] = $mean_norm_val;
					$cond->quant_dict_control_normalized_p_value[$key] = $mean_norm_p_val;
				}
			}
		}
	}
}
function DoOutlierAnalysis($branch_id, $master_id_list, $db)
{
	$query = "SELECT A.condition_id, B.fold_change_control_norm, B.p_value_control_norm, B.unique_identifier_id FROM project_conditions AS A JOIN data_descriptive_statistics AS B ON A.condition_id=B.condition_id WHERE A.branch_id=:branch_id AND A.is_control=0";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	$temp_meas_dict = array();
	foreach ($row as $entry) {
		$fc = $entry['fold_change_control_norm']; $pval = $entry['p_value_control_norm']; $cond_id = $entry['condition_id']; $id = $entry['unique_identifier_id'];
		if (!array_key_exists($id, $temp_meas_dict))
		{
			$temp_meas_dict[$id] = array();
		}
		array_push($temp_meas_dict[$id], array('condition_id' => $cond_id, 'pval' => $pval, 'fc' => $fc));
	}

	$outlier_data = array();

	foreach ($temp_meas_dict as $key => $value) {
		//$key = molecule identifier | $value = all data
		$up_list = array();
		$down_list = array();
		foreach ($value as $entry) {
			$cond_id = $entry['condition_id']; $pval = $entry['pval']; $fc = $entry['fc'];
			if ($pval!==1 && $pval!=="1")
			{
				$fc < 0 ? array_push($down_list, array("fc"=> $fc, "pval" => -log($pval, 10), "cond_id" => $cond_id)) : array_push($up_list, array("fc"=> $fc, "pval" => -log($pval, 10), "cond_id" => $cond_id));
			}
		}

		if (count($up_list) > 0)
		{
			if (count($up_list)>1)
			{
				//up list uGPS
				$p_values = array_column($up_list, 'pval');
				$fcs = array_column($up_list, 'fc');
				$max_pval = max($p_values);
				$max_fc = max($fcs);

				for ($i = 0; $i < count($up_list); $i++)
				{
					$entry = $up_list[$i];
					$adj_pval = $entry['pval']/$max_pval;
					$adj_fc = $entry['fc']/$max_fc;
					$orig_distance = sqrt(pow($adj_pval, 2) + pow($adj_fc,2));
					$up_list[$i]['adj_pval'] = $adj_pval;
					$up_list[$i]['adj_fc'] = $adj_fc;
					$up_list[$i]['orig_distance'] = $orig_distance;
				}

				usort($up_list, function($a, $b)
				{
					return ($a['orig_distance']<$b['orig_distance']);
				});

				$max_outlier = null;
				$min_distance = 50000000;

				for ($i = 0; $i < 3; $i++)
				{
					if ($i >= count($up_list))
					{
						break;
					}
					$curr_entry = $up_list[$i];
					if ($curr_entry['pval']< -log(0.05, 10))
					{
						continue;
					}
					foreach ($up_list as $other_entry) {
						if ($other_entry['cond_id']===$curr_entry['cond_id'])
						{
							continue;
						}
						if (abs($other_entry['fc']) > abs($curr_entry['fc']))
						{
							continue;
						}
						$fc_diff = abs($other_entry['adj_fc'] - $curr_entry['adj_fc']);
						$pval_diff = abs($other_entry['adj_pval']-$curr_entry['adj_pval']);
						$curr_diff = sqrt(pow($fc_diff,2) + pow($pval_diff, 2));
						if ($curr_diff < $min_distance)
						{
							$min_distance = $curr_diff;
							$max_outlier = $curr_entry;
						}
					}
				}

				if (!empty($max_outlier))
				{
					array_push($outlier_data, array("regulation" => "UP", "max_condition_id" => $max_outlier['cond_id'], "distance" => $min_distance, "algorithm" => "UGPS", "unique_identifier_id" => $key));
				}
			}
			else
			{
				//up list single meas
				$distance = sqrt(pow($up_list[0]['fc'],2) + pow($up_list[0]['pval'],2));
				$up_list[0]['pval']>= -log(0.05, 10) ? array_push($outlier_data, array("regulation" => "UP", "max_condition_id" => $up_list[0]['cond_id'], "distance" => $distance, "algorithm" => "SINGLE", "unique_identifier_id" => $key)) : null;
			}
		}
		if (count($down_list)>0)
		{
			if (count($down_list)>1)
			{
				//down list uGPS
				$p_values = array_column($down_list, 'pval');
				$fcs = array_column($down_list, 'fc');
				$max_pval = max($p_values);
				$max_fc = min($fcs);

				for ($i = 0; $i < count($down_list); $i++)
				{
					$entry = $down_list[$i];
					$adj_pval = $entry['pval']/$max_pval;
					$adj_fc = $entry['fc']/$max_fc;
					$orig_distance = sqrt(pow($adj_pval, 2) + pow($adj_fc,2));
					$down_list[$i]['adj_pval'] = $adj_pval;
					$down_list[$i]['adj_fc'] = $adj_fc;
					$down_list[$i]['orig_distance'] = $orig_distance;
				}

				usort($down_list, function($a, $b)
				{
					return ($a['orig_distance']<$b['orig_distance']);
				});

				$max_outlier = null;
				$min_distance = 50000000;

				for ($i = 0; $i < 3; $i++)
				{
					if ($i >= count($down_list))
					{
						break;
					}
					$curr_entry = $down_list[$i];
					if ($curr_entry['pval']< -log(0.05, 10))
					{
						continue;
					}
					foreach ($down_list as $other_entry) {
						if ($other_entry['cond_id']===$curr_entry['cond_id'])
						{
							continue;
						}
						if (abs($other_entry['fc']) > abs($curr_entry['fc']))
						{
							continue;
						}
						$fc_diff = abs($other_entry['adj_fc'] - $curr_entry['adj_fc']);
						$pval_diff = abs($other_entry['adj_pval']-$curr_entry['adj_pval']);
						$curr_diff = sqrt(pow($fc_diff,2) + pow($pval_diff, 2));
						if ($curr_diff < $min_distance)
						{
							$min_distance = $curr_diff;
							$max_outlier = $curr_entry;
						}
					}
				}

				if (!empty($max_outlier))
				{
					array_push($outlier_data, array("regulation" => "DOWN", "max_condition_id" => $max_outlier['cond_id'], "distance" => $min_distance, "algorithm" => "UGPS", "unique_identifier_id" => $key));
				}

			}
			else
			{
				//down list single meas
				$distance = sqrt(pow($down_list[0]['fc'],2) + pow($down_list[0]['pval'],2));
				$down_list[0]['pval']>= -log(0.05, 10) ? array_push($outlier_data, array("regulation" => "DOWN", "max_condition_id" => $down_list[0]['cond_id'], "distance" => $distance, "algorithm" => "SINGLE", "unique_identifier_id" => $key)) : null;
			}
		}
	}
	return $outlier_data;
}
function DoMultipleTestingCorrection($condition_dict)
{
	foreach ($condition_dict as $cond) {
		if (count($cond->quant_dict_control_normalized_p_value)===0)
		{
			continue;
		}
		//adjust control normalized p-values
		$control_p_value_array = array();
		foreach ($cond->quant_dict_control_normalized_p_value as $key => $value) {
			array_push($control_p_value_array, $value);
		}
		rsort($control_p_value_array);
		$corrected_p_values_control = array();
		array_push($corrected_p_values_control, $control_p_value_array[0]);
		for ($i = 1; $i < count($control_p_value_array); $i++)
		{
			$coefficient = ((double)count($control_p_value_array))/((double)count($control_p_value_array)-(double)$i);
			$curr_p_value = $control_p_value_array[$i] * $coefficient;
			$last_p_value = end($corrected_p_values_control);
			array_push($corrected_p_values_control, min($curr_p_value, $last_p_value));
		}
		$control_p_value_mapping_array = array();
		for ($i = 0; $i<count($control_p_value_array); $i++)
		{
			$original_p_value = $control_p_value_array[$i];
			$corrected_p_value = $corrected_p_values_control[$i];
			if(!array_key_exists((string)$original_p_value, $control_p_value_mapping_array))
			{
				$control_p_value_mapping_array[(string)$original_p_value] = $corrected_p_value;
			}
		}
		$entries = count($cond->quant_dict_control_normalized_p_value);
		foreach ($cond->quant_dict_control_normalized_p_value as $key => $value) {
			$fdr_p_value = $control_p_value_mapping_array[(string)$value];
			$bonferroni_p_value = min(1, ($value * ((double)$entries)));
			$cond->quant_dict_control_normalized_p_value_fdr[$key] = $fdr_p_value;
			$cond->quant_dict_control_normalized_p_value_bonferroni[$key] = $bonferroni_p_value;
		}

		//adjust mean normalized p-values
		$mean_p_value_array = array();
		foreach ($cond->quant_dict_mean_normalized_p_value as $key => $value) {
			array_push($mean_p_value_array, $value);
		}
		rsort($mean_p_value_array);
		$corrected_p_values_mean = array();
		array_push($corrected_p_values_mean, $mean_p_value_array[0]);
		for ($i = 1; $i < count($mean_p_value_array); $i++)
		{
			$coefficient = ((double)count($mean_p_value_array))/((double)count($mean_p_value_array)-(double)$i);
			$curr_p_value = $mean_p_value_array[$i] * $coefficient;
			$last_p_value = end($corrected_p_values_mean);
			array_push($corrected_p_values_mean, min($curr_p_value, $last_p_value));
		}
		$mean_p_value_mapping_array = array();
		for ($i = 0; $i<count($mean_p_value_array); $i++)
		{
			$original_p_value = $mean_p_value_array[$i];
			$corrected_p_value = $corrected_p_values_mean[$i];
			if(!array_key_exists((string)$original_p_value, $mean_p_value_mapping_array))
			{
				$mean_p_value_mapping_array[(string)$original_p_value] = $corrected_p_value;
			}
		}
		$entries = count($cond->quant_dict_mean_normalized_p_value);
		foreach ($cond->quant_dict_mean_normalized_p_value as $key => $value) {
			$fdr_p_value = $mean_p_value_mapping_array[(string)$value];
			$bonferroni_p_value = min(1, ($value * ((double)$entries)));
			$cond->quant_dict_mean_normalized_p_value_fdr[$key] = $fdr_p_value;
			$cond->quant_dict_mean_normalized_p_value_bonferroni[$key] = $bonferroni_p_value;
		}
	}
}
function WriteTruncatedFile($file_array, $identifier_indexes, $feature_descriptor_header_indexes, $quant_header_indexes, $quant, $feature_descriptors, $identifier, $truncated_file_path, $file_id, $db, $project_id, $condition_dict, $master_id_list)
{
	$query = "SELECT truncated_file_name FROM project_files WHERE file_id=:file_id";
	$query_params = array(':file_id' => $file_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if($row)
	{
		$old_file_name = $row['truncated_file_name'];
		//$old_file_path = 'server/php/files/' . $project_id . '/' . $old_file_name;
		$old_file_path = $old_file_name;
		if (file_exists($old_file_path) && !empty($old_file_name))
		{
			unlink($old_file_path);
		}
	}

	$rotated_file_array = $file_array;
	TransposeArray($rotated_file_array);
	$relevant_index_array = array();
	$column_to_username_array = array();
	$quant_header_obj = json_decode($quant, true);
	$feature_descriptors_obj = json_decode($feature_descriptors, true);
	$identifier_obj = json_decode($identifier, true);
	$identifier_index = -1;
	foreach($quant_header_obj as $val)
	{
		$column_to_username_array[$val['header']] = $val['repName'] . " Quant Values";
	}
	if (count($feature_descriptors_obj)> 0)
	{
		foreach($feature_descriptors_obj as $val)
		{
			$column_to_username_array[$val['header']] = $val['userName'];
		}
	}
	foreach($identifier_obj as $val)
	{
		$column_to_username_array[$val['header']] = $val['userName'];
	}
	foreach ($identifier_indexes as $key => $value) {
		array_push($relevant_index_array, $value);
		$identifier_index = $value;
	}
	foreach ($feature_descriptor_header_indexes as $key => $value) {
		array_push($relevant_index_array, $value);
	}
	foreach ($quant_header_indexes as $key => $value) {
		array_push($relevant_index_array, $value);
	}
	sort($relevant_index_array);

	$write_file_array = array();
	foreach ($relevant_index_array as $value) {
		array_push($write_file_array, $rotated_file_array[$value]);
	}
	TransposeArray($write_file_array);

	$index_to_rep_dict = array();
	for ($i = 0; $i < count($write_file_array[0]); $i++)
	{
		$curr_header = $write_file_array[0][$i];
		foreach ($condition_dict as $cond) {
			foreach ($cond->replicate_list as $rep) {
				$rep_header = $rep->header_text;
				if ($rep_header===$curr_header)
				{
					$index_to_rep_dict[$i] = $rep;
					break;
				}
			}
		}
		$write_file_array[0][$i] = $column_to_username_array[$curr_header];
	}

	//$complete_truncated_file_path = 'server/php/files/' . $project_id . '/' . $truncated_file_path;
	$complete_truncated_file_path =  $truncated_file_path;
	$file_obj = fopen($complete_truncated_file_path, "w");

	for ($i = 0; $i < count($write_file_array); $i++)
	{
		$curr_line = "";
		for ($j = 0; $j < count($write_file_array[$i]); $j++)
		{
			if ($i===0)
			{
				$curr_line .= $write_file_array[$i][$j] . "\t";
			}
			else
			{
				if (array_key_exists($j, $index_to_rep_dict))
				{
					$curr_identifier = $master_id_list[$file_array[$i][$identifier_index]];
					$curr_rep = $index_to_rep_dict[$j];
					$curr_quant_val = 0;
					array_key_exists($curr_identifier, $curr_rep->quant_dict) ? $curr_quant_val = $curr_rep->quant_dict[$curr_identifier] : null;
					$curr_line .= $curr_quant_val . "\t";
				}
				else
				{
					$curr_line .= $write_file_array[$i][$j] . "\t";
				}
			}
		}
		$curr_line .= "\n";
		fwrite($file_obj, $curr_line);
	}
	fclose($file_obj);

	//need to put some logic in here to delete the current truncated file and point towards this new one
	$query = "UPDATE project_files SET truncated_file_name=:truncated_file_name WHERE file_id=:file_id";
	$query_params = array(':file_id' => $file_id, ':truncated_file_name' => $truncated_file_path);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}
function AddReplicateDataToDatabase($condition_dict, $projectID, $set_id, $branch_id, $file_id, $db, $standard_id_mappings)
{
	foreach ($condition_dict as $cond) {
		foreach ($cond->replicate_list as $rep) {
			$insertArray = array();
			foreach ($rep->quant_dict as $key => $value) {
				$is_imputed = 0; 
				$rep->quant_dict[$key]===$rep->quant_dict_pre_imputation[$key] ? $is_imputed = 0 : $is_imputed = 1;
				array_push($insertArray, array($projectID, $key, $value, $value, pow(2, $value), $rep->replicate_id, $cond->condition_id, $set_id, $branch_id, $file_id, $is_imputed, $standard_id_mappings[$key]));
			}
			$chunked_array = array_chunk($insertArray, 2000);

			foreach ($chunked_array as $chunk) {
				$row_length = count($chunk[0]);
				$nb_rows = count($chunk);
				$length = $row_length * $nb_rows;
				$args = implode(',', array_map(
					function($el) { return '('.implode(',', $el).')'; },
					array_chunk(array_fill(0, $length, '?'), $row_length)
					));

				$query_params = array();
				foreach($chunk as $array)
				{
					foreach($array as $value)
					{
						$query_params[] = $value;
					}
				}
				$insertText = "INSERT INTO data_replicate_data (project_id, unique_identifier_id, quant_value, quant_value_log2, quant_value_raw, replicate_id, condition_id, set_id, branch_id, file_id, is_imputed, standard_molecule_id) VALUES " . $args;
				try{
					$stmt = $db->prepare($insertText);
					$result = $stmt->execute($query_params);
				}
				catch (PDOException $ex) {
					//die("Failed to run query: " . $ex->getMessage());
				}
			}
			$rep->quant_dict = null;
		}
	}
}
function AddConditionDataToDatabase($condition_dict, $projectID, $set_id, $branch_id, $file_id, $db, $standard_id_mappings)
{
	foreach ($condition_dict as $cond) {
		$insertArray = array();
		foreach ($cond->quant_dict_all_vals as $key => $value) {
			array_push($insertArray, array($projectID, $key, $cond->quant_dict_avg_val[$key], implode(";", $value), $cond->quant_dict_sds[$key], $cond->quant_dict_cvs[$key], count($value), $cond->condition_id, $set_id, $branch_id, $file_id, $standard_id_mappings[$key]));
		}
		$chunked_array = array_chunk($insertArray, 2000);
		foreach ($chunked_array as $chunk) {
			$row_length = count($chunk[0]);
			$nb_rows = count($chunk);
			$length = $row_length * $nb_rows;
			$args = implode(',', array_map(
				function($el) { return '('.implode(',', $el).')'; },
				array_chunk(array_fill(0, $length, '?'), $row_length)
				));

			$query_params = array();
			foreach($chunk as $array)
			{
				foreach($array as $value)
				{
					$query_params[] = $value;
				}
			}
			$insertText = "INSERT INTO data_condition_data (project_id, unique_identifier_id, avg_quant_value, all_quant_values, std_dev_quant_value, cv_quant_values, num_quant_values, condition_id, set_id, branch_id, file_id, standard_molecule_id) VALUES " . $args;
			try{
				$stmt = $db->prepare($insertText);
				$result = $stmt->execute($query_params);
			}
			catch (PDOException $ex) {
					//die("Failed to run query: " . $ex->getMessage());
			}
		}
	}
}
function AddDataDescriptiveStatisticsToDatabase($condition_dict, $projectID, $set_id, $branch_id, $file_id, $db, $standard_id_mappings)
{
	foreach ($condition_dict as $cond) {
		if ($cond->is_control===1)
		{
			continue;
		}
		$insertArray = array();
		foreach ($cond->quant_dict_control_normalized as $key => $value) {
			array_push($insertArray, array($projectID, $file_id, $set_id, $branch_id, $cond->condition_id, $key, $cond->quant_dict_avg_val[$key], $cond->quant_dict_mean_normalized[$key], $value, $cond->quant_dict_mean_normalized_p_value[$key],
				$cond->quant_dict_control_normalized_p_value[$key], $cond->quant_dict_sds[$key], $cond->quant_dict_control_normalized_p_value_bonferroni[$key], $cond->quant_dict_mean_normalized_p_value_bonferroni[$key],
				$cond->quant_dict_control_normalized_p_value_fdr[$key], $cond->quant_dict_mean_normalized_p_value_fdr[$key], $standard_id_mappings[$key]));
		}
		$chunked_array = array_chunk($insertArray, 2000);
		foreach ($chunked_array as $chunk) {
			$row_length = count($chunk[0]);
			$nb_rows = count($chunk);
			$length = $row_length * $nb_rows;
			$args = implode(',', array_map(
				function($el) { return '('.implode(',', $el).')'; },
				array_chunk(array_fill(0, $length, '?'), $row_length)
				));

			$query_params = array();
			foreach($chunk as $array)
			{
				foreach($array as $value)
				{
					$query_params[] = $value;
				}
			}
			$insertText = "INSERT INTO data_descriptive_statistics (project_id, file_id, set_id, branch_id, condition_id, unique_identifier_id, quant_val, fold_change_mean_norm, fold_change_control_norm, p_value_mean_norm, p_value_control_norm, std_dev, bonferroni_p_value_control_norm, bonferroni_p_value_mean_norm, fdr_p_value_control_norm, fdr_p_value_mean_norm, standard_molecule_id) VALUES " . $args;
			$stmt = $db->prepare($insertText);
			$result = $stmt->execute($query_params);
		}
	}
}
function AddPCADataToDatabase($pca_results, $db, $projectID, $set_id, $branch_id, $is_condition=true)
{
	if ($is_condition)
	{
		$deleteText = "DELETE FROM data_pca_condition WHERE branch_id=:branch_id";
		$deleteParams = array(':branch_id' => $branch_id);
		$delStmt = $db->prepare($deleteText);
		$result = $delStmt->execute($deleteParams);
		
		$insertArray = array();
		foreach ($pca_results as $entry) {
			array_push($insertArray, array($projectID, $entry['condition_id'], $branch_id, $set_id, $entry['component_number'], $entry['scaled_vector'], $entry['variance_fraction']));

		}
		$chunked_array = array_chunk($insertArray, 2000);
		foreach ($chunked_array as $chunk) {
			$row_length = count($chunk[0]);
			$nb_rows = count($chunk);
			$length = $row_length * $nb_rows;
			$args = implode(',', array_map(
				function($el) { return '('.implode(',', $el).')'; },
				array_chunk(array_fill(0, $length, '?'), $row_length)
				));

			$query_params = array();
			foreach($chunk as $array)
			{
				foreach($array as $value)
				{
					$query_params[] = $value;
				}
			}
			$insertText = "INSERT INTO data_pca_condition (project_id, condition_id, branch_id, set_id, component_number, scaled_vector, variance_fraction) VALUES " . $args;
			$stmt = $db->prepare($insertText);
			$result = $stmt->execute($query_params);
		}
	}
	else
	{
		$deleteText = "DELETE FROM data_pca_replicate WHERE branch_id=:branch_id";
		$deleteParams = array(':branch_id' => $branch_id);
		$delStmt = $db->prepare($deleteText);
		$result = $delStmt->execute($deleteParams);
		
		$insertArray = array();
		foreach ($pca_results as $entry) {
			array_push($insertArray, array($projectID, $branch_id, $set_id, $entry['condition_id'], $entry['replicate_id'], $entry['component_number'], $entry['scaled_vector'], $entry['variance_fraction']));

		}
		$chunked_array = array_chunk($insertArray, 2000);
		foreach ($chunked_array as $chunk) {
			$row_length = count($chunk[0]);
			$nb_rows = count($chunk);
			$length = $row_length * $nb_rows;
			$args = implode(',', array_map(
				function($el) { return '('.implode(',', $el).')'; },
				array_chunk(array_fill(0, $length, '?'), $row_length)
				));

			$query_params = array();
			foreach($chunk as $array)
			{
				foreach($array as $value)
				{
					$query_params[] = $value;
				}
			}
			$insertText = "INSERT INTO data_pca_replicate (project_id, branch_id, set_id, condition_id, replicate_id, component_number, scaled_vector, variance_fraction) VALUES " . $args;
			$stmt = $db->prepare($insertText);
			$result = $stmt->execute($query_params);
		}
	}
}
function AddOutlierDataToDatabase($outlier_data, $projectID, $branch_id, $db)
{
	$deleteText = "DELETE FROM data_outlier_analysis WHERE branch_id=:branch_id";
	$deleteParams = array(':branch_id' => $branch_id);
	$delStmt = $db->prepare($deleteText);
	$result = $delStmt->execute($deleteParams);

	$insertArray = array();
	foreach ($outlier_data as $entry) {
		array_push($insertArray, array($projectID, $branch_id, $entry['unique_identifier_id'], $entry['regulation'], $entry['max_condition_id'], $entry['distance'], $entry['algorithm']));
	}
	$chunked_array = array_chunk($insertArray, 2000);
	foreach ($chunked_array as $chunk) {
		$row_length = count($chunk[0]);
		$nb_rows = count($chunk);
		$length = $row_length * $nb_rows;
		$args = implode(',', array_map(
			function($el) { return '('.implode(',', $el).')'; },
			array_chunk(array_fill(0, $length, '?'), $row_length)
			));

		$query_params = array();
		foreach($chunk as $array)
		{
			foreach($array as $value)
			{
				$query_params[] = $value;
			}
		}
		$insertText = "INSERT INTO data_outlier_analysis (project_id, branch_id, unique_identifier_id, regulation, max_regulated_condition_id, distance, algorithm) VALUES " . $args;
		$stmt = $db->prepare($insertText);
		$result = $stmt->execute($query_params);
	}
}
function UpdateQueryTerms($condition_dict, $master_id_list, $db, $projectID, $set_name, $branch_id, $set_id)
{
	$query = "SELECT * FROM data_query_terms WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	$current_terms = array();
	foreach ($row as $entry) {
		$current_terms[$entry['query_term_text']] = $entry;
	}

	$insertArray = array();
	foreach ($master_id_list as $key => $value) {
		$curr_term = $key . " (Molecule)";
		if (!array_key_exists($curr_term, $current_terms))
		{
			array_push($insertArray, array($curr_term, $value, -1, -1, -1, -1, $projectID, -1));
		}
	}
	foreach ($condition_dict as $cond) {
		$curr_cond_term = $cond->condition_name . " (" . $set_name . ") (Condition)";
		if (!array_key_exists($curr_cond_term, $current_terms))
		{
			array_push($insertArray, array($curr_cond_term, -1, -1, $cond->condition_id, $set_id, $branch_id, $projectID, -1));
		}
		foreach ($cond->replicate_list as $rep) {
			$curr_rep_term =  $rep->replicate_name . " (" . $set_name . ") (Replicate)";
			if (!array_key_exists($curr_rep_term, $current_terms))
			{
				array_push($insertArray, array($curr_rep_term, -1, $rep->replicate_id, $cond->condition_id, $set_id, $branch_id, $projectID, -1));
			}
		}
	}
	$chunked_array = array_chunk($insertArray, 2000);
	foreach ($chunked_array as $chunk) {
		$row_length = count($chunk[0]);
		$nb_rows = count($chunk);
		$length = $row_length * $nb_rows;
		$args = implode(',', array_map(
			function($el) { return '('.implode(',', $el).')'; },
			array_chunk(array_fill(0, $length, '?'), $row_length)
			));

		$query_params = array();
		foreach($chunk as $array)
		{
			foreach($array as $value)
			{
				$query_params[] = $value;
			}
		}
		$insertText = "INSERT INTO data_query_terms (query_term_text, unique_identifier_id, replicate_id, condition_id, set_id, branch_id, project_id, has_control) VALUES " . $args;
		$stmt = $db->prepare($insertText);
		$result = $stmt->execute($query_params);
	}
}
function UpdateProjectDataSummary($branch_id, $projectID, $db)
{
	//This must be done post file add in order to handle multiple files on same branch

	//Values to be filled
	$replicate_count = 0; $condition_count= 0; $set_count=0; $quant_meas_count = 0; $avg_meas_per_rep_count = 0; $avg_meas_overlap_cond = 0; $avg_rep_cv = 0;

	//Get replicate count
	$query = "SELECT COUNT(*) FROM project_replicates WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if ($row)
	{
		$replicate_count = $row["COUNT(*)"];
	}
	
	//Get condition count
	$query = "SELECT COUNT(*) FROM project_conditions WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if ($row)
	{
		$condition_count = $row["COUNT(*)"];
	}

	//Get set count
	$query = "SELECT COUNT(*) FROM project_sets WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if ($row)
	{
		$set_count = $row["COUNT(*)"];
	}

	//Get measurement count
	//SELECT COUNT(*) FROM data_replicate_data AS a JOIN project_conditions AS b ON a.condition_id=b.condition_id WHERE a.branch_id='ai01gJR'
	$query = "SELECT COUNT(*) FROM data_replicate_data AS a JOIN project_conditions AS b ON a.condition_id=b.condition_id WHERE a.branch_id=:branch_id";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if ($row)
	{
		$quant_meas_count = $row["COUNT(*)"];
	}

	//Get average cv
	$query = "SELECT AVG(a.cv_quant_values) FROM data_condition_data AS a JOIN project_conditions AS b on a.condition_id=b.condition_id WHERE a.branch_id=:branch_id";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if ($row)
	{
		$avg_rep_cv = $row["AVG(a.cv_quant_values)"];
	}

	//Get avg measurements per rep
	//SELECT COUNT(a.unique_identifier_id), a.replicate_id FROM data_replicate_data AS a JOIN project_conditions AS b ON a.condition_id=b.condition_id WHERE a.branch_id='ai01gJR-1B' GROUP BY a.replicate_id
	$query = "SELECT COUNT(a.unique_identifier_id), a.replicate_id FROM data_replicate_data AS a JOIN project_conditions AS b ON a.condition_id=b.condition_id WHERE a.branch_id=:branch_id GROUP BY a.replicate_id";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	$all_rep_meas_counts = array();
	if ($row)
	{
		foreach ($row as $entry) {
			array_push($all_rep_meas_counts, $entry['COUNT(a.unique_identifier_id)']);
		}
	}
	$avg_meas_per_rep_count = Mean($all_rep_meas_counts);

	//Get avg measurements per cond **store cond ids for later processing
	$query = "SELECT COUNT(a.unique_identifier_id), a.condition_id FROM data_condition_data AS a JOIN project_conditions AS b ON a.condition_id=b.condition_id WHERE a.branch_id=:branch_id GROUP BY condition_id";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	$all_cond_meas_counts = array();
	$all_cond_ids = array();
	if ($row)
	{
		foreach ($row as $entry) {
			array_push($all_cond_meas_counts, $entry['COUNT(a.unique_identifier_id)']);
			array_push($all_cond_ids, $entry['condition_id']);
		}
	}
	$avg_meas_per_cond_count = Mean($all_cond_meas_counts);

	$avg_overlap_counts = array();
	for ($i= 0; $i < count($all_cond_ids); $i++)
	{
		for ($j = $i+1; $j < count($all_cond_ids); $j++)
		{
			$id_1 = $all_cond_ids[$i];
			$id_2 = $all_cond_ids[$j];
			$query = "SELECT COUNT(*) FROM (SELECT t1.unique_identifier_id FROM data_condition_data t1 INNER JOIN data_condition_data t2 ON t1.unique_identifier_id = t2.unique_identifier_id WHERE t1.condition_id=:id_1 AND t2.condition_id=:id_2 AND t1.branch_id=:branch_id_1 AND t2.branch_id=:branch_id_2) I";
			$query_params = array(':id_1' => $id_1, ':id_2' => $id_2, ':branch_id_2' => $branch_id, ':branch_id_1' => $branch_id);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
			$row = $stmt->fetch();
			if ($row)
			{
				array_push($avg_overlap_counts, $row['COUNT(*)']);
			}
		}
	}
	$avg_meas_overlap_cond = Mean($avg_overlap_counts);

	$query = "DELETE FROM project_data_summary WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$query = "INSERT INTO project_data_summary (project_id, branch_id, replicate_count, condition_count, set_count, quant_measurement_count, avg_meas_per_rep, avg_meas_per_cond, avg_meas_overlap_cond, avg_rep_cv) VALUES 
	(:project_id, :branch_id, :replicate_count, :condition_count, :set_count, :quant_measurement_count, :avg_meas_per_rep, :avg_meas_per_cond, :avg_meas_overlap_cond, :avg_rep_cv)";

	$query_params = array(':project_id' => $projectID, ':branch_id' => $branch_id, ':replicate_count' => $replicate_count, ':condition_count' => $condition_count, ':set_count' => $set_count,
		':quant_measurement_count' => $quant_meas_count, ':avg_meas_per_rep' => $avg_meas_per_cond_count, ':avg_meas_per_cond' => $avg_meas_per_rep_count, 
		':avg_meas_overlap_cond' =>  $avg_meas_overlap_cond, ':avg_rep_cv' => $avg_rep_cv);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}
function AddFeatureMetaDataToDatabase($file_array, $master_id_list, $db, $projectID, $identifier_indexes, $feature_descriptor_header_indexes, $feature_descriptors)
{
	$current_data = array();
	$query = "SELECT unique_identifier_id FROM data_feature_metadata WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	if ($row)
	{
		foreach ($row as $entry) {
			$current_data[$entry['unique_identifier_id']] = "";
		}
	}

	$feature_descriptors_obj = json_decode($feature_descriptors, true);
	$feature_column_name_to_username = array();

	foreach ($feature_descriptors_obj as $entry) {
		$feature_column_name_to_username[$entry['header']] = $entry['userName'];
	}

	$insertArray = array();

	$mol_identifier_index = -1;
	foreach ($identifier_indexes as $key => $value) {
		$mol_identifier_index = $value;
	}

	for ($i = 1; $i < count($file_array); $i++)
	{
		$mol_name = $file_array[$i][$mol_identifier_index];
		$mol_unique_id = $master_id_list[$mol_name];
		if (!array_key_exists($mol_unique_id, $current_data))
		{
			foreach ($feature_descriptor_header_indexes as $key => $value) {
				$column_name = $key;
				$feature_name = $feature_column_name_to_username[$column_name];
				$feature_value = $file_array[$i][$value];
				array_push($insertArray, array($projectID, $mol_unique_id, $feature_name, $feature_value));
			}
		}
	}
	$chunked_array = array_chunk($insertArray, 2000);
	foreach ($chunked_array as $chunk) {
		$row_length = count($chunk[0]);
		$nb_rows = count($chunk);
		$length = $row_length * $nb_rows;
		$args = implode(',', array_map(
			function($el) { return '('.implode(',', $el).')'; },
			array_chunk(array_fill(0, $length, '?'), $row_length)
			));

		$query_params = array();
		foreach($chunk as $array)
		{
			foreach($array as $value)
			{
				$query_params[] = $value;
			}
		}
		$insertText = "INSERT INTO data_feature_metadata (project_id, unique_identifier_id, feature_metadata_name, feature_metadata_text) VALUES " . $args;
		$stmt = $db->prepare($insertText);
		$result = $stmt->execute($query_params);
	}
}
function GetPCAQuantDict($db, $projectID, $branch_id)
{
	$tmp_cond_dict = array();
	//query all conditions
	$query = "SELECT condition_id, condition_name FROM project_conditions WHERE project_id=:project_id AND branch_id=:branch_id";
	$query_params = array(':project_id' => $projectID, ':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	if(!$row)
	{
		return null;
	}

	foreach($row as $entry)
	{
		$currCond = new Condition();
		$currCond->condition_id = $entry['condition_id'];
		$currCond->condition_name = $entry['condition_name'];
		$tmp_cond_dict[$entry['condition_id']] = $currCond;
	}

	$final_cond_dict = array();

	foreach ($tmp_cond_dict as $cond) {
		$query = "SELECT unique_identifier_id, avg_quant_value FROM data_condition_data WHERE condition_id=:condition_id";
		$query_params = array(':condition_id' => $cond->condition_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		if ($row)
		{
			foreach ($row as $entry) {
				$cond->quant_dict_avg_val[$entry['unique_identifier_id']] = $entry['avg_quant_value'];
			}
			$final_cond_dict[$cond->condition_id] = $cond;
		}
	}

	foreach ($final_cond_dict as $cond) {
		$query = "SELECT a.quant_value, a.unique_identifier_id, a.replicate_id FROM data_replicate_data AS a JOIN project_replicates AS b ON a.replicate_id=b.replicate_id WHERE a.condition_id=:condition_id";
		$query_params = array(':condition_id' => $cond->condition_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		if ($row)
		{
			foreach ($row as $entry) {
				if (!array_key_exists($entry['replicate_id'], $cond->replicate_list))
				{
					$currRep = new Replicate();
					$currRep->replicate_id = $entry['replicate_id'];
					$cond->replicate_list[$entry['replicate_id']] = $currRep;
				}
				
				$cond->replicate_list[$entry['replicate_id']]->quant_dict[$entry['unique_identifier_id']] = $entry['quant_value'];
			}
		}
	}

	return $final_cond_dict;
}
function CheckForExistingWork($branch_id, $projectID, $db)
{
	$query = "SELECT COUNT(*) FROM project_sets WHERE branch_id=:branch_id AND project_id=:project_id";
	$query_params = array(':branch_id' => $branch_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$count = $row['COUNT(*)'];

	return $count > 0 ? true : false;
}


//Update process_queue table
function FinishProcess($process_id, $db)
{
	//Update current process--the loop will catch the next one if it exists on the next go around.
	$update_text = "UPDATE process_queue SET completed=1, running=0, task_completion_time=:time WHERE process_id=:process_id";
	$query_params = array(':process_id' => $process_id, ':time' => date("Y-m-d H:i:s"));
	try{
		$stmt = $db->prepare($update_text);
		$result = $stmt->execute($query_params);
	}
	catch (PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
}
function UpdateProgress($process_id, $progress, $db)
{
	//LockTable($db);
	$update_text = "UPDATE process_queue SET progress=:progress WHERE process_id=:process_id";
	$query_params = array(':process_id' => $process_id, ':progress' => (int)$progress);
	try{
		$stmt = $db->prepare($update_text);
		$result = $stmt->execute($query_params);
	}
	catch (PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
	//UnlockTable($db);
}
function UpdateProcessStatus($process_id, $db)
{
	//Update current process--the loop will catch the next one if it exists on the next go around.
	$update_text = "UPDATE process_queue SET running=1 WHERE process_id=:process_id";
	$query_params = array(':process_id' => $process_id);
	try{
		$stmt = $db->prepare($update_text);
		$result = $stmt->execute($query_params);
	}
	catch (PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
}
function LockTable($db)
{
	$lockText = "LOCK TABLES process_queue WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}
function UnlockTable($db)
{
	$unlockText = "UNLOCK TABLES";
	$stmt = $db->prepare($unlockText);
	$result = $stmt->execute();
}

//Math Helper functions
function CoefficientOfVariation($array, $is_log2=true)
{
	if($is_log2)
	{
		$tmp_array = array(); foreach ($array as $val) {
			array_push($tmp_array, (2**$val));
		}
		$cv = (100 * (stats_standard_deviation($tmp_array, true))/Mean($tmp_array));
		return ($cv);
	}
	else
	{
		$cv = (100 * (stats_standard_deviation($array, true))/Mean($array));
		return ($cv);
	}
}
function Mean($arr)
{
	return array_sum($arr)/count($arr);
}
function tTestTwoSample($array1, $array2)
{
	if(empty($array1) || empty($array2)) { return 1;}
	if(count($array1)===0 || count($array2)===0) { return 1;}
	$μ₁ = Mean($array1); $μ₂ = Mean($array2); $n₁ = count($array1); $n₂ = count($array2);
	$σ₁ = stats_standard_deviation($array1, true); $σ₂ =stats_standard_deviation($array2, true);

	$Δ = 0;
	$num = ($μ₁ - $μ₂ - $Δ);
	$denom = sqrt((($σ₁**2) / $n₁) + (($σ₂**2) / $n₂));
	if ($denom==0)
	{
		return 1;
	}
	$t = $num / $denom;
        // Degrees of freedom
	$v = ($n₁ - 1) + ($n₂ - 1);
	$pval = Tcall(buzz(abs($t), $v));
	return $pval;
}
function TCall($x)
{
	return $x;
}
function buzz($t,$n) {

  $pi=pi();
  $pj2=$pi/2;
  $pj4=$pi/4;
  $pi2=2*$pi;
  $e = exp(1);
  $exx = 1.10517091807564;
  $dgr=180/$pi;

	$t=abs($t);
	$rt=$t/sqrt($n);
	$fk=atan($rt);
	if($n==1) { return 1-$fk/$pj2; }
	$ek=sin($fk); $dk=cos($fk);
	if(($n%2)==1)
		{ return 1-($fk+$ek*$dk*zip($dk*$dk,2,$n-3,-1))/$pj2; }
	else
		{ return 1-$ek*zip($dk*$dk,1,$n-3,-1); }
}
function zip($q,$i,$j,$b) {
	$zz=1;
	$z=$zz;
	$k=$i;
	while($k<=$j) { $zz=$zz*$q*$k/($k-$b); $z=$z+$zz; $k=$k+2; }
	return $z;
}
function Abuzz($p,$n) { $v=0.5; $dv=0.5; $t=0;
	while($dv>1e-6) { $t=1/$v-1; $dv=$dv/2; if(buzz($t,$n)>$p) { $v=$v-$dv; } else { $v=$v+$dv; } }
	return $t;
}

//Classes
class Condition {
	public $condition_name = "";
	public $is_control="";
	public $replicate_list = array();
	public $quant_dict_all_vals = array();
	public $quant_dict_avg_val = array();
	public $quant_dict_cvs = array();
	public $quant_dict_sds = array();
	public $quant_dict_mean_normalized = array();
	public $quant_dict_mean_normalized_p_value = array();
	public $quant_dict_mean_normalized_p_value_fdr = array();
	public $quant_dict_mean_normalized_p_value_bonferroni = array();
	public $quant_dict_control_normalized = array();
	public $quant_dict_control_normalized_p_value = array();
	public $quant_dict_control_normalized_p_value_fdr = array();
	public $quant_dict_control_normalized_p_value_bonferroni = array();
	public $file_id = -1;
	public $projectID = "";
	public $condition_id = "";
}

class Replicate
{
	public $header_text ="";
	public $condition_name = "";
	public $replicate_name = "";
	public $is_control=0;
	public $quant_dict = array();
	public $quant_dict_pre_imputation = array();
	public $file_id = -1;
	public $projectID = "";
	public $replicate_id = "";
}

