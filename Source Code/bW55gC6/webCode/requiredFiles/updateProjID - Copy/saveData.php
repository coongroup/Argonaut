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

//query all information from temp files/temp file headers
$query = "SELECT * FROM project_temporary_files WHERE uploader_user_id=:user_id AND project_id=:project_id LIMIT 1";
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

$temporary_file_id=$row['temporary_file_id'];
$og_file_name=$row['original_file_name'];
$file_name=$row['file_name'];
$upload_time=$row['upload_time'];

$query = "SELECT * FROM project_temporary_file_headers WHERE uploader_user_id=:user_id AND project_id=:project_id AND temporary_file_id=:temporary_file_id LIMIT 1";
$query_params = array(':user_id' => $_SESSION['user'],
	':project_id' => $projectID, ':temporary_file_id' => $temporary_file_id);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	header("Location: main.php");
	die("Redirecting to main.php");
}

$identifier = $row['identifier'];
$feature_descriptors = $row['feature_descriptors'];
$quant = $row['quant'];
$log2_transform = $row['log2_transform'];
$impute = $row['impute_missing_values'];
$filter = $row['filter'];
$branch_id= $row['branch_id'];
$set_name = $row['set_name'];
$delimiter = $row['delimiter'];

//lock write project files

$lockText = "LOCK TABLES project_files WRITE";
$stmt = $db->prepare($lockText);
$result = $stmt->execute();

//add file info to project files

$insertText = "INSERT INTO project_files (project_id, uploader_user_id, original_file_name, file_name, delimiter, upload_time, impute_missing_values, log2_transform, identifier, 
	feature_descriptors, quant, filter, branch_id, set_name) VALUES (:project_id, :uploader_user_id, :original_file_name, :file_name, :delimiter, :upload_time, :impute_missing_values,
	:log2_transform, :identifier, :feature_descriptors, :quant, :filter, :branch_id, :set_name)";
$query_params = array(':project_id' => $projectID, ':uploader_user_id' => $_SESSION['user'], ':original_file_name' => $og_file_name, ':file_name' => $file_name, ':delimiter' => $delimiter,
	':upload_time' => $upload_time, ':impute_missing_values' => $impute, ':log2_transform' => $log2_transform, ':identifier' => $identifier, ':feature_descriptors' => $feature_descriptors, 
	':quant' => $quant, ':filter' => $filter, ':branch_id' => $branch_id, ':set_name' => $set_name);

try{
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);
}
catch (PDOException $ex) {
	die("Failed to run query: " . $ex->getMessage());
}

//get file id
$file_id = $db->lastInsertID();

//unlock project files

$unlockText = "UNLOCK TABLES";
$stmt = $db->prepare($unlockText);
$result = $stmt->execute();

//parse file headers

$identifier_header_array = json_decode($identifier, true);
$feature_descriptor_header_array = json_decode($feature_descriptors, true);
$quant_header_array = json_decode($quant, true);

//lock file headers

$lockText = "LOCK TABLES project_file_headers WRITE";
$stmt = $db->prepare($lockText);
$result = $stmt->execute();

//add each header to file headers table

$insertArray = array();
foreach ($identifier_header_array as $header) {
	array_push($insertArray, array($_SESSION['user'], $projectID, $file_id, $header['header'], $header['userName'], 1,0,0, "", "", 0));
}
foreach ($feature_descriptor_header_array as $header) {
	array_push($insertArray, array($_SESSION['user'], $projectID, $file_id, $header['header'], $header['userName'], 0,1,0, "", "", 0));
}
foreach ($quant_header_array as $header) {
	array_push($insertArray, array($_SESSION['user'], $projectID, $file_id, $header['header'], "", 0,0,1, $header['condName'], $header['repName'], $header['control']==="Yes" ? 1 : 0));
}

$row_length = count($insertArray[0]);
$nb_rows = count($insertArray);
$length = $row_length * $nb_rows;

$args = implode(',', array_map(
	function($el) { return '('.implode(',', $el).')'; },
	array_chunk(array_fill(0, $length, '?'), $row_length)
	));

$query_params = array();
foreach($insertArray as $array)
{
	foreach($array as $value)
	{
		$query_params[] = $value;
	}
}

$insertText = "INSERT INTO project_file_headers (uploader_user_id, project_id, file_id, header_text, user_header_name, is_unique_id, is_feature_metadata, is_quant_data, condition_name, replicate_name, is_control) VALUES " . $args;

try{
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);
}
catch (PDOException $ex) {
	die("Failed to run query: " . $ex->getMessage());
}

//unlock file headers

$unlockText = "UNLOCK TABLES";
$stmt = $db->prepare($unlockText);
$result = $stmt->execute();

//lock set table

$lockText = "LOCK TABLES project_sets WRITE, project_files WRITE";
$stmt = $db->prepare($lockText);
$result = $stmt->execute();

//get last set number
$max_set = 1;
$query = "SELECT MAX(set_number) FROM project_sets WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
$max_set=1;
if($row)
{
	is_numeric($row['MAX(set_number)']) ? $max_set=$row['MAX(set_number)']+1 : null;
}

//add set
$set_id = ($projectID . "-" . $max_set . "S");
$insertText = "INSERT INTO project_sets (set_id, project_id, branch_id, set_name, set_number, file_id, use_data) VALUES (:set_id, :project_id, :branch_id, :set_name, :set_number, :file_id, :use_data)";
$query_params = array(':set_id' => $set_id, ':project_id' => $projectID, ':branch_id' => $branch_id, 
	':set_name' => $set_name, ':set_number' => $max_set, ':file_id' => $file_id, ':use_data' => 1);
try{
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);
}
catch (PDOException $ex) {
	die("Failed to run query: " . $ex->getMessage());
}

//update project files with set_id
$updateText = "UPDATE project_files SET set_id=:set_id WHERE file_id=:file_id";
$query_params = array(':set_id'=> $set_id, ':file_id' => $file_id);
try{
	$stmt = $db->prepare($updateText);
	$result = $stmt->execute($query_params);
}
catch (PDOException $ex) {
	die("Failed to run query: " . $ex->getMessage());
}

//unlock set table
$unlockText = "UNLOCK TABLES";
$stmt = $db->prepare($unlockText);
$result = $stmt->execute();

//lock conditions table

$lockText = "LOCK TABLES project_conditions WRITE";
$stmt = $db->prepare($lockText);
$result = $stmt->execute();

//get last condition_number

$query = "SELECT MAX(condition_number) FROM project_conditions WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
$max_condition=1;
if($row)
{
	is_numeric($row['MAX(condition_number)']) ? $max_condition=$row['MAX(condition_number)']+1 : null;
}

//add each condition

$insertArray = array();
$used_condition_array = array();
foreach ($quant_header_array as $header) {
	if (!array_key_exists($header['condName'], $used_condition_array))
	{
		$condition_id = $projectID . "-" . $max_condition . "C";
		$is_control = $header['control']=="Yes" ? 1 : 0;
		array_push($insertArray, array($condition_id, $header['condName'], $max_condition, $projectID, $file_id, $is_control, $set_id, $branch_id, 1));
		$used_condition_array[$header['condName']] = $condition_id;
		$max_condition++;
	}
}

$row_length = count($insertArray[0]);
$nb_rows = count($insertArray);
$length = $row_length * $nb_rows;

$args = implode(',', array_map(
	function($el) { return '('.implode(',', $el).')'; },
	array_chunk(array_fill(0, $length, '?'), $row_length)
	));

$query_params = array();
foreach($insertArray as $array)
{
	foreach($array as $value)
	{
		$query_params[] = $value;
	}
}

$insertText = "INSERT INTO project_conditions (condition_id, condition_name, condition_number, project_id, file_id, is_control, set_id, branch_id, use_data) VALUES " . $args;

try{
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);
}
catch (PDOException $ex) {
	die("Failed to run query: " . $ex->getMessage());
}

//unlock conditions table

$unlockText = "UNLOCK TABLES";
$stmt = $db->prepare($unlockText);
$result = $stmt->execute();

//lock replicates table
$lockText = "LOCK TABLES project_replicates WRITE";
$stmt = $db->prepare($lockText);
$result = $stmt->execute();

//get last replicate number
$query = "SELECT MAX(replicate_number) FROM project_replicates WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
$max_replicate=1;
if($row)
{
	is_numeric($row['MAX(replicate_number)']) ? $max_replicate=$row['MAX(replicate_number)']+1 : null;
}

//add replicates
$insertArray = array();
foreach($quant_header_array as $header)
{
	$replicate_id = $projectID . "-" . $max_replicate . "R";
	$condition_id = $used_condition_array[$header['condName']];
	$is_control = $header['control']=="Yes" ? 1 : 0;
	array_push($insertArray, array($replicate_id, $header['repName'], $max_replicate, $projectID, $file_id, $is_control, $condition_id, $set_id, $branch_id, 1, $header['header']));
	$max_replicate++;
}

$row_length = count($insertArray[0]);
$nb_rows = count($insertArray);
$length = $row_length * $nb_rows;

$args = implode(',', array_map(
	function($el) { return '('.implode(',', $el).')'; },
	array_chunk(array_fill(0, $length, '?'), $row_length)
	));

$query_params = array();
foreach($insertArray as $array)
{
	foreach($array as $value)
	{
		$query_params[] = $value;
	}
}

$insertText = "INSERT INTO project_replicates (replicate_id, replicate_name, replicate_number, project_id, file_id, is_control, condition_id, set_id, branch_id, use_data, header_text) VALUES " . $args;
try{
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);
}
catch (PDOException $ex) {
	die("Failed to run query: " . $ex->getMessage());
}

//unlock replicates table
$unlockText = "UNLOCK TABLES";
$stmt = $db->prepare($unlockText);
$result = $stmt->execute();

//clear project_temporary_files
try
{
	$query = "DELETE FROM project_temporary_files WHERE uploader_user_id=:user_id AND project_id=:project_id";
	$query_params = array(':user_id' => $_SESSION['user'],
		':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}
catch (PDOException $ex)
{
	die("Failed to run query: " . $ex->getMessage());
}

//clear project_temporary_file_headers
$query = "DELETE FROM project_temporary_file_headers WHERE uploader_user_id=:user_id AND project_id=:project_id";
try
{
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params); 
}
catch (PDOException $ex) 
{
	die("Failed to run query: " . $ex->getMessage());
}

//lock process_queue table
$lockText = "LOCK TABLES process_queue WRITE";
$stmt = $db->prepare($lockText);
$result = $stmt->execute();

//add new entry to process_queue table
$insertText = "INSERT INTO process_queue (user_id, project_id, set_id, task, task_params, running, completed, task_creation_time, task_completion_time) VALUES 
(:user_id, :project_id, :set_id, :task, :task_params, :running, :completed, :task_creation_time, :task_completion_time)";
$query_params = array(':user_id' => $_SESSION['user'], ':project_id' => $projectID, ':set_id' => $set_id, ':task' => 'UPLOAD', ':task_params' => "", ':running' => 0,
	':completed' => 0, ':task_creation_time' => date("Y-m-d H:i:s"), ':task_completion_time' => "");
try
{
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params); 
}
catch (PDOException $ex) 
{
	die("Failed to run query: " . $ex->getMessage());
}

//check if there is a task running on this project

$query = "SELECT * FROM process_queue WHERE running=1 AND project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

//if no then launch a new worker
if(!$row)
{
	//launch a new worker here
	//http://www.developertutorials.com/running-background-processes-in-php/ -- how to launch
	//http://stackoverflow.com/questions/6826718/pass-variable-to-php-script-running-from-command-line -- how to pass arguments
}

//if yes then proceed--the active worker will take care of the new task once it finishes its current process.

//unlock process_queue
$unlockText = "UNLOCK TABLES";
$stmt = $db->prepare($unlockText);
$result = $stmt->execute();
