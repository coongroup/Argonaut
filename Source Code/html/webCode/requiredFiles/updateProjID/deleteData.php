<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant

//FIX THIS UP HERE BEFORE YOU PROCEED.

$projectID=-1;
//$type = $_POST['t'];
$name = strtolower($_POST['n']);
$id=$_POST['i'];
$currentName = $_POST['n'];
$nodeType = $_POST['t'];


//$id='wZjXehg-2B';
//$nodeType="Branch";
//$name = "protein-pellet";
//$currentName="Protein-Pellet";
$time = date("Y-m-d H:i:s");

//Return an array indicating success/failure, and a text statement.
$data = array();

//Confirm edit abilities (permission_level 2 or 3)
$query = "SELECT * FROM project_permissions WHERE user_id=:user AND project_id=:project_id AND permission_level>=2";
$query_params = array(':user' => $_SESSION['user'], ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	$data = array("result"=>false, "message"=>"You do not have permission to delete any project data.");
	echo(json_encode($data));
	return;
}

//Confirm rename is either Project, Branch, Set, Condition, or Replicate (merged with code below).

//Confirm that existing project, branch, etc. has the appropriate name.
switch($nodeType)
{
	case "Project":
	$query = "SELECT * FROM projects WHERE project_id=:project_id AND project_name=:name";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName);
	$data = array("result"=>false, "message"=>"Unable to delete project.");
	echo(json_encode($data));
	return;
	break;
	case "Branch":
	$query = "SELECT * FROM project_branches WHERE project_id=:project_id AND branch_name=:name AND branch_id=:id";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName, ':id'=> $id);
	break;
	case "Set":
	$query = "SELECT * FROM project_sets WHERE project_id=:project_id AND set_name=:name AND set_id=:id";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName, ':id'=>$id);
	break;
	case "Condition":
	$query = "SELECT * FROM project_conditions WHERE project_id=:project_id AND condition_name=:name AND condition_id=:id";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName, ':id'=>$id);
	break;
	case "Replicate":
	$query = "SELECT * FROM project_replicates WHERE project_id=:project_id AND replicate_name=:name AND replicate_id=:id";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName, ':id'=>$id);
	break;
	default:
	$data = array("result"=>false, "message"=>"Specified node type cannot be found.");
	echo(json_encode($data));
	return;
	break;
}
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	$data = array("result"=>false, "message"=>"No " . $nodeType . " named " . $currentName . " was found.");
	echo(json_encode($data));
	return;
}

//if deleting a replicate or a condition, you need to delete the set and re-add the file
if ($nodeType==="Replicate")
{
	//get set
	$query = "SELECT a.set_id, b.set_name, c.branch_name, c.branch_id FROM project_replicates AS a JOIN project_sets AS b ON a.set_id=b.set_id JOIN project_branches AS c ON c.branch_id=a.branch_id WHERE a.replicate_id=:id";
	$query_params = array(':id'=>$id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		$data = array("result"=>false, "message"=>"Unable to find dataset associated with " . $currentName . ".");
		echo(json_encode($data));
		return;
	}
	$set_id=$row['set_id'];
	$set_name=$row['set_name'];
	$branch_name=$row['branch_name'];
	$branch_id=$row['branch_id'];

	//get set file upload data
	$file_upload_data = GetFileUploadInfo($set_id, $db);
	//echo(json_encode($file_upload_data));

	//adjust set file upload data
	$quant_adj = array();
	$quant_obj = json_decode($file_upload_data['quant'], true);
	foreach ($quant_obj as $entry) {
		$entry['repName']===$currentName ? null : array_push($quant_adj, $entry);
	}

	//add conditions, replicates, set, file, and update process queue. FILE FIRST TO GET FILE ID!!!!

	$new_file_id=AddNewFileEntry($file_upload_data, $quant_adj, $db);

	$new_set_id = AddNewNodes($set_name, $quant_adj, $projectID, $new_file_id, $branch_id, $db);

	//delete conditions, replicates, and set.
	DeleteSetsBySetID($set_id, $db);
	DeleteConditionsBySetID($set_id, $db);
	DeleteReplicatesBySetID($set_id, $db);
	DeleteQueryTermsBySetID($set_id, $db);


	//Update file id
	$query = "UPDATE project_files SET set_id=:set_id WHERE file_id=:file_id";
	$query_params = array(':set_id' => $new_set_id, ':file_id' => $new_file_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add deleted replicate activity
	//Deleted replicate 'rep name' (Branch: branch name | Set: set name)
	$delete_description = "Deleted replicate '" . $currentName . "' (Branch: " . $branch_name . " | Set: " . $set_name . ")";
	$query = "INSERT INTO project_activity (project_id, activity, time, description) VALUES (:project_id, :activity, :time, :description)";
	$query_params = array(':project_id' => $projectID, ':activity' => 'REPLICATE DELETE', ':time' => $time, ':description' => $delete_description);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add set to data_deletion_queue
	$query = "INSERT INTO data_deletion_queue (project_id, identifier, identifier_type, deletion_time) VALUES (:project_id, :identifier, :identifier_type, :time)";
	$query_params = array(':project_id' => $projectID, ':identifier' => $set_id, ':identifier_type' => 'set_id', ':time' => $time);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add to process queue
	$lockText = "LOCK TABLES process_queue WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
	$query = "INSERT INTO process_queue (user_id, project_id, set_id, branch_id, task, task_params, running, completed, task_creation_time, task_completion_time) VALUES 
	(:user_id, :project_id, :set_id, :branch_id, :task, :task_params, :running, :completed, :task_creation_time, :task_completion_time)";
	$query_params = array(':user_id' => $file_upload_data['uploader_user_id'], ':project_id' => $projectID, ':task_params' => '', ':task' => 'REPROCESS', ':set_id' => $new_set_id, ':branch_id' => $branch_id,
		':running' => '0', ':completed' => '0', ':task_creation_time' => $time, ':task_completion_time' => '');
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//start processing worker if none exists.

	$query = "SELECT * FROM process_queue WHERE running=1 AND project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();

	//if no then launch a new worker
	if(!$row)
	{
		$ch = curl_init();
 
		curl_setopt($ch, CURLOPT_URL, "127.0.0.1/DV/" . $projectID . "/advancedProcess.php");
		//curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		 
		curl_exec($ch);
		curl_close($ch);
	}

	//if yes then proceed--the active worker will take care of the new task once it finishes its current process.

	//unlock process_queue
	$unlockText = "UNLOCK TABLES";
	$stmt = $db->prepare($unlockText);
	$result = $stmt->execute();

	//return a message
	//Data from file ___ is being reprocessed to reflect the removal of 'rep name'. Data from this file will become available once processing has completed.
	$data = array("result"=>true, "message"=>"Data from the file '" . $file_upload_data['original_file_name'] . "' is being reprocessed to reflect the removal of replicate '" . $currentName . "'. Data from this file will become available once processing routines have completed.");
	echo(json_encode($data));
	return;
}


if ($nodeType==="Condition")
{
	//get set
	$query = "SELECT a.set_id, b.set_name, c.branch_name, c.branch_id FROM project_conditions AS a JOIN project_sets AS b ON a.set_id=b.set_id JOIN project_branches AS c ON c.branch_id=a.branch_id WHERE a.condition_id=:id";
	$query_params = array(':id'=>$id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		$data = array("result"=>false, "message"=>"Unable to find dataset associated with " . $currentName . ".");
		echo(json_encode($data));
		return;
	}
	$set_id=$row['set_id'];
	$set_name=$row['set_name'];
	$branch_name=$row['branch_name'];
	$branch_id=$row['branch_id'];


	//get set file upload data
	$file_upload_data = GetFileUploadInfo($set_id, $db);
	//echo(json_encode($file_upload_data));

	//adjust set file upload data
	$quant_adj = array();
	$quant_obj = json_decode($file_upload_data['quant'], true);
	foreach ($quant_obj as $entry) {
		$entry['condName']===$currentName ? null : array_push($quant_adj, $entry);
	}

	//add conditions, replicates, set, file, and update process queue. FILE FIRST TO GET FILE ID!!!!

	$new_file_id=AddNewFileEntry($file_upload_data, $quant_adj, $db);

	$new_set_id = AddNewNodes($set_name, $quant_adj, $projectID, $new_file_id, $branch_id, $db);

	//delete conditions, replicates, and set.
	DeleteSetsBySetID($set_id, $db);
	DeleteConditionsBySetID($set_id, $db);
	DeleteReplicatesBySetID($set_id, $db);
	DeleteQueryTermsBySetID($set_id, $db);

	//Update file id
	$query = "UPDATE project_files SET set_id=:set_id WHERE file_id=:file_id";
	$query_params = array(':set_id' => $new_set_id, ':file_id' => $new_file_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add deleted condition activity

	$delete_description = "Deleted condition '" . $currentName . "' (Branch: " . $branch_name . " | Set: " . $set_name . ")";
	$query = "INSERT INTO project_activity (project_id, activity, time, description) VALUES (:project_id, :activity, :time, :description)";
	$query_params = array(':project_id' => $projectID, ':activity' => 'CONDITION DELETE', ':time' => $time, ':description' => $delete_description);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add set to data_deletion_queue
	$query = "INSERT INTO data_deletion_queue (project_id, identifier, identifier_type, deletion_time) VALUES (:project_id, :identifier, :identifier_type, :time)";
	$query_params = array(':project_id' => $projectID, ':identifier' => $set_id, ':identifier_type' => 'set_id', ':time' => $time);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add to process queue
	$lockText = "LOCK TABLES process_queue WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
	$query = "INSERT INTO process_queue (user_id, project_id, set_id, branch_id, task, task_params, running, completed, task_creation_time, task_completion_time) VALUES 
	(:user_id, :project_id, :set_id, :branch_id, :task, :task_params, :running, :completed, :task_creation_time, :task_completion_time)";
	$query_params = array(':user_id' => $file_upload_data['uploader_user_id'], ':project_id' => $projectID, ':task_params' => '', ':task' => 'REPROCESS', ':set_id' => $new_set_id, ':branch_id' => $branch_id,
		':running' => '0', ':completed' => '0', ':task_creation_time' => $time, ':task_completion_time' => '');
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//start processing worker if none exists.

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
		$ch = curl_init();
 
		curl_setopt($ch, CURLOPT_URL, "127.0.0.1/DV/" . $projectID . "/advancedProcess.php");
		//curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		 
		curl_exec($ch);
		curl_close($ch);
	}

	//if yes then proceed--the active worker will take care of the new task once it finishes its current process.

	//unlock process_queue
	$unlockText = "UNLOCK TABLES";
	$stmt = $db->prepare($unlockText);
	$result = $stmt->execute();

	//return a message
	//Data from file ___ is being reprocessed to reflect the removal of 'rep name'. Data from this file will become available once processing has completed.
	$data = array("result"=>true, "message"=>"Data from the file '" . $file_upload_data['original_file_name'] . "' is being reprocessed to reflect the removal of condition '" . $currentName . "'. Data from this file will become available once processing routines have completed.");
	echo(json_encode($data));
	return;
}

//if deleting a set you just need to delete the set w/ no upload

if($nodeType==="Set")
{
	//query branch name and branch id
	$query = "SELECT a.set_name, b.branch_name, b.branch_id FROM project_sets AS a JOIN project_branches as b on a.branch_id=b.branch_id WHERE a.set_id=:id";
	$query_params = array(':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		$data = array("result"=>false, "message"=>"Unable to find dataset associated with " . $currentName . ".");
		echo(json_encode($data));
		return;
	}

	$set_name=$row['set_name'];
	$branch_name=$row['branch_name'];
	$branch_id=$row['branch_id'];
	$set_id = $id;

	//delete conditions, replicates, and set
	DeleteSetsBySetID($set_id, $db);
	DeleteConditionsBySetID($set_id, $db);
	DeleteReplicatesBySetID($set_id, $db);
	DeleteQueryTermsBySetID($set_id, $db);
	//DeleteFilesBySetID($set_id, $db);

	//add deleted set activity
	$delete_description = "Deleted set '" . $currentName . "' (Branch: " . $branch_name . ")";
	$query = "INSERT INTO project_activity (project_id, activity, time, description) VALUES (:project_id, :activity, :time, :description)";
	$query_params = array(':project_id' => $projectID, ':activity' => 'SET DELETE', ':time' => $time, ':description' => $delete_description);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add set to data_deletion_queue

	$query = "INSERT INTO data_deletion_queue (project_id, identifier, identifier_type, deletion_time) VALUES (:project_id, :identifier, :identifier_type, :time)";
	$query_params = array(':project_id' => $projectID, ':identifier' => $set_id, ':identifier_type' => 'set_id', ':time' => $time);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add to process queue
	$lockText = "LOCK TABLES process_queue WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
	$query = "INSERT INTO process_queue (user_id, project_id, branch_id, task, task_params, running, completed, task_creation_time, task_completion_time) VALUES 
	(:user_id, :project_id, :branch_id, :task, :task_params, :running, :completed, :task_creation_time, :task_completion_time)";
	$query_params = array(':user_id' => $_SESSION['user'], ':project_id' => $projectID, ':task_params' => '', ':task' => 'EDIT', ':branch_id' => $branch_id,
		':running' => '0', ':completed' => '0', ':task_creation_time' => $time, ':task_completion_time' => '');
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//start processing worker if none exists.

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
		$ch = curl_init();
 
		curl_setopt($ch, CURLOPT_URL, "127.0.0.1/DV/" . $projectID . "/advancedProcess.php");
		//curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		 
		curl_exec($ch);
		curl_close($ch);
	}

	//if yes then proceed--the active worker will take care of the new task once it finishes its current process.

	//unlock process_queue
	$unlockText = "UNLOCK TABLES";
	$stmt = $db->prepare($unlockText);
	$result = $stmt->execute();

	$data = array("result"=>true, "message"=>"Data from set '" . $currentName . "' has been removed from the project. Some analyses are currently being repeated to reflect the most up-to-date project data.");
	echo(json_encode($data));
	return;

	//You need to redo pca and outlier analyses now.
}

//if deleting a branch you need to delete all the sets.

if($nodeType==="Branch")
{
		//LOCK HERE
	$lockText = "LOCK TABLES project_max_nodes WRITE, project_branches WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
	
	//get max branch id number and branch count
	$query = "SELECT project_max_nodes.max_branch_number, COUNT(project_branches.branch_number) FROM project_max_nodes JOIN project_branches ON project_max_nodes.project_id=project_branches.project_id WHERE project_max_nodes.project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	
	$max_branch_number = $row['max_branch_number']++;
	$total_branch_count = $row['COUNT(project_branches.branch_number)'];

	//delete conditions, replicates, sets, and branch
	$branch_id = $id;
	DeleteSetsByBranchID($branch_id, $db);
	DeleteConditionsByBranchID($branch_id, $db);
	DeleteReplicatesByBranchID($branch_id, $db);
	DeleteBranchesByBranchID($branch_id, $db);
	DeleteQueryTermsByBranchID($branch_id, $db);
	DeleteProcessesByBranchID($branch_id, $db);
	DeleteFilesByBranchID($branch_id, $db);

	//add deleted branch activity
	$delete_description = "Deleted branch '" . $currentName . "'";
	$query = "INSERT INTO project_activity (project_id, activity, time, description) VALUES (:project_id, :activity, :time, :description)";
	$query_params = array(':project_id' => $projectID, ':activity' => 'BRANCH DELETE', ':time' => $time, ':description' => $delete_description);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//add branch to data_deletion_queue

	$query = "INSERT INTO data_deletion_queue (project_id, identifier, identifier_type, deletion_time) VALUES (:project_id, :identifier, :identifier_type, :time)";
	$query_params = array(':project_id' => $projectID, ':identifier' => $branch_id, ':identifier_type' => 'branch_id', ':time' => $time);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//if this was the only branch you need to add another default branch
	if($total_branch_count===1 || $total_branch_count==="1")
	{
		$max_branch_number++;
		$new_branch_id = $projectID . "-" . $max_branch_number . "B";
		$query = "INSERT INTO project_branches (branch_id, project_id, branch_number, branch_name, use_data) VALUES (:branch_id, :project_id, :branch_number, :branch_name, :use_data)";
		$query_params = array(':branch_id'=> $new_branch_id, ':project_id' => $projectID, ':branch_number'=> $max_branch_number, ':branch_name' => 'Main', ':use_data' => 1);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
	}

	//Update project_max_nodes
	$query = "UPDATE project_max_nodes SET max_branch_number=:max_branch WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID, ':max_branch'=>$max_branch_number);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//unlock project conditions
	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$data = array("result"=>true, "message"=>"Data from branch '" . $currentName . "' has been removed from the project.");
	echo(json_encode($data));
	return;
}

//if deleting a project you need to delete all the sets, and all the branches (you can handle that here)

//ClearDatabase($db);
//AddToDatabase($db);

function GetFileUploadInfo($set_id, $db)
{
	$query = "SELECT * FROM project_files WHERE set_id=:id";
	$query_params = array(':id'=>$set_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	return $row;
}

function DeleteFilesByBranchID($branch_id, $db)
{
	$lockText = "LOCK TABLES project_files WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_files WHERE branch_id=:id";
	$query_params = array(':id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteProcessesByBranchID($branch_id, $db)
{
	$lockText = "LOCK TABLES process_queue WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM process_queue WHERE branch_id=:id";
	$query_params = array(':id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteBranchesByBranchID($branch_id, $db)
{
	$lockText = "LOCK TABLES project_branches WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_branches WHERE branch_id=:id";
	$query_params = array(':id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteSetsByBranchID($branch_id, $db)
{
	$lockText = "LOCK TABLES project_sets WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_sets WHERE branch_id=:id";
	$query_params = array(':id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteConditionsByBranchID($branch_id, $db)
{
	$lockText = "LOCK TABLES project_conditions WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_conditions WHERE branch_id=:id";
	$query_params = array(':id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteReplicatesByBranchID($branch_id, $db)
{
	$lockText = "LOCK TABLES project_replicates WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_replicates WHERE branch_id=:id";
	$query_params = array(':id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteQueryTermsByBranchID($branch_id, $db)
{
	$lockText = "LOCK TABLES data_query_terms WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM data_query_terms WHERE branch_id=:id";
	$query_params = array(':id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteSetsBySetID($set_id, $db)
{
	$lockText = "LOCK TABLES project_sets WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_sets WHERE set_id=:id";
	$query_params = array(':id' => $set_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteConditionsBySetID($set_id, $db)
{
	$lockText = "LOCK TABLES project_conditions WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_conditions WHERE set_id=:id";
	$query_params = array(':id' => $set_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteReplicatesBySetID($set_id, $db)
{
	$lockText = "LOCK TABLES project_replicates WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_replicates WHERE set_id=:id";
	$query_params = array(':id' => $set_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteQueryTermsBySetID($set_id, $db)
{
	$lockText = "LOCK TABLES data_query_terms WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM data_query_terms WHERE set_id=:id";
	$query_params = array(':id' => $set_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function DeleteFilesBySetID($set_id, $db)
{
	$lockText = "LOCK TABLES project_files WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	$query="DELETE FROM project_files WHERE set_id=:id";
	$query_params = array(':id' => $set_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}

function AddNewNodes($set_name, $quant_data, $project_id, $new_file_id, $branch_id, $db)
{
	//lock project sets
	$lockText = "LOCK TABLES project_sets WRITE, project_files WRITE, project_max_nodes WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	//get max set number
	$query = "SELECT max_set_number FROM project_max_nodes WHERE project_ID=:project_id";
	$query_params = array(':project_id' => $project_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$max_set=1;
	if($row)
	{
		is_numeric($row['max_set_number']) ? $max_set=$row['max_set_number']+1 : null;
	}

	//create new set entry
	$new_set_id= ($project_id . "-" . $max_set . "S");
	$insertText = "INSERT INTO project_sets (set_id, project_id, branch_id, set_name, set_number, file_id, use_data) VALUES (:set_id, :project_id, :branch_id, :set_name, :set_number, :file_id, :use_data)";
	$query_params = array(':set_id' => $new_set_id, ':project_id' => $project_id, ':branch_id' => $branch_id, 
		':set_name' => $set_name, ':set_number' => $max_set, ':file_id' => $new_file_id, ':use_data' => 1);
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);

	//Update project_max_nodes
	$query = "UPDATE project_max_nodes SET max_set_number=:max_set WHERE project_id=:project_id";
	$query_params = array(':project_id' => $project_id, ':max_set'=>$max_set);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//unlock project sets
	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	//lock project conditions
	$lockText = "LOCK TABLES project_conditions WRITE, project_max_nodes WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	//get make condition number
	$query = "SELECT max_condition_number FROM project_max_nodes WHERE project_id=:project_id";
	$query_params = array(':project_id' => $project_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$max_condition=1;
	if($row)
	{
		is_numeric($row['max_condition_number']) ? $max_condition=$row['max_condition_number']+1 : null;
	}

	//insert project conditions
	$insertArray = array();
	$used_condition_array = array();
	foreach ($quant_data as $header) 
	{
		if (!array_key_exists($header['condName'], $used_condition_array))
		{
			$condition_id = $project_id . "-" . $max_condition . "C";
			$is_control = $header['control']=="Yes" ? 1 : 0;
			array_push($insertArray, array($condition_id, $header['condName'], $max_condition, $project_id, $new_file_id, $is_control, $new_set_id, $branch_id, 1));
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
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);

	//Update project_max_nodes
	$query = "UPDATE project_max_nodes SET max_condition_number=:max_cond WHERE project_id=:project_id";
	$query_params = array(':project_id' => $project_id, ':max_cond'=>$max_condition);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//unlock project conditions
	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	//lock project replicates
	$lockText = "LOCK TABLES project_replicates WRITE, project_max_nodes WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	//get max replicate number
	$query = "SELECT max_replicate_number FROM project_max_nodes WHERE project_id=:project_id";
	$query_params = array(':project_id' => $project_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$max_replicate=1;
	if($row)
	{
		is_numeric($row['max_replicate_number']) ? $max_replicate=$row['max_replicate_number']+1 : null;
	}

	//insert replicates
	$insertArray = array();
	foreach($quant_data as $header)
	{
		$replicate_id = $project_id . "-" . $max_replicate . "R";
		$condition_id = $used_condition_array[$header['condName']];
		$is_control = $header['control']=="Yes" ? 1 : 0;
		array_push($insertArray, array($replicate_id, $header['repName'], $max_replicate, $project_id, $new_file_id, $is_control, $condition_id, $new_set_id, $branch_id, 1, $header['header']));
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
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);

	//Update project_max_nodes
	$query = "UPDATE project_max_nodes SET max_replicate_number=:max_rep WHERE project_id=:project_id";
	$query_params = array(':project_id' => $project_id, ':max_rep'=>$max_replicate);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//unlock replicates
	$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

	return $new_set_id;

}

//Re-adds file and file headers--setting up for a new upload.
function AddNewFileEntry($file_upload_data, $quant, $db)
{
	$lockText = "LOCK TABLES project_files WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
	$insertText = "INSERT INTO project_files (project_id, uploader_user_id, original_file_name, file_name, delimiter, upload_time, impute_missing_values, log2_transform, identifier, 
		feature_descriptors, quant, filter, branch_id, set_name, organism_id, standard_id_column, standard_id_type) VALUES (:project_id, :uploader_user_id, :original_file_name, :file_name, :delimiter, :upload_time, :impute_missing_values,
		:log2_transform, :identifier, :feature_descriptors, :quant, :filter, :branch_id, :set_name, :organism_id, :standard_id_column, :standard_id_type)";
	$query_params = array(':project_id' => $file_upload_data['project_id'], ':uploader_user_id' => $file_upload_data['uploader_user_id'], ':original_file_name' => $file_upload_data['original_file_name'],
		':file_name' => $file_upload_data['file_name'], ':delimiter' => $file_upload_data['delimiter'],
		':upload_time' => $file_upload_data['upload_time'], ':impute_missing_values' => $file_upload_data['impute_missing_values'], ':log2_transform' => $file_upload_data['log2_transform'],
		':identifier' => $file_upload_data['identifier'], ':feature_descriptors' => $file_upload_data['feature_descriptors'], 
		':quant' => json_encode($quant), ':filter' => $file_upload_data['filter'], ':branch_id' => $file_upload_data['branch_id'], ':set_name' => $file_upload_data['set_name'],
		 ':organism_id' => $file_upload_data['organism_id'], ':standard_id_column' => $file_upload_data['standard_id_column'], ':standard_id_type' => $file_upload_data['standard_id_type']);
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);
	$file_id = $db->lastInsertID();

	$unlockText = "UNLOCK TABLES";
	$stmt = $db->prepare($unlockText);
	$result = $stmt->execute();

	$identifier_header_array = json_decode($file_upload_data['identifier'], true);
	$feature_descriptor_header_array = json_decode($file_upload_data['feature_descriptors'], true);
	$quant_header_array = $quant;
	$project_id = $file_upload_data['project_id'];

	$insertArray = array();
	foreach ($identifier_header_array as $header) {
		array_push($insertArray, array($_SESSION['user'], $project_id, $file_id, $header['header'], $header['userName'], 1,0,0, "", "", 0));
	}
	foreach ($feature_descriptor_header_array as $header) {
		array_push($insertArray, array($_SESSION['user'], $project_id, $file_id, $header['header'], $header['userName'], 0,1,0, "", "", 0));
	}
	foreach ($quant_header_array as $header) {
		array_push($insertArray, array($_SESSION['user'], $project_id, $file_id, $header['header'], "", 0,0,1, $header['condName'], $header['repName'], $header['control']==="Yes" ? 1 : 0));
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
	$stmt = $db->prepare($insertText);
	$result = $stmt->execute($query_params);

	return $file_id;

}
