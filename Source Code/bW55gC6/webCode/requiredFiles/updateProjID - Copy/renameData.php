<?php

require("config.php");

if(empty($_SESSION['user']))
{
//   header("Location: index.html");
	die();
}

$projectID=-1;
$nodeType = $_POST['t'];
$newName = $_POST['n'];
$id = $_POST['i'];
$currentName = $_POST['cn'];

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
	$data = array("result"=>false, "message"=>"You do not have permission to rename any project data.");
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

//Add an entry to project activity.
$query = "INSERT INTO project_activity (project_id, activity, time, description) VALUES (:project_id, :activity, :time, :description)";
$query_params = array(':project_id' => $projectID, ':activity' => $nodeType . " Name Update", ':time' => date("Y-m-d H:i:s"), ':description' => $nodeType . " name update. " . $currentName . " changed to " . $newName . ".");
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);


//Update the name.
switch($nodeType)
{
	case "Project":
	$query = "UPDATE projects SET project_name=:newname WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName);
	break;
	case "Branch":
	$query = "UPDATE project_branches SET branch_name=:newname WHERE project_id=:project_id AND branch_id=:id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName, ':id' => $id);
	break;
	case "Set":
	$query = "UPDATE project_sets SET set_name=:newname WHERE project_id=:project_id AND set_id=:id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName, ':id' => $id);
	break;
	case "Condition":
	$query = "UPDATE project_conditions SET condition_name=:newname WHERE project_id=:project_id AND condition_id=:id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName, ':id' => $id);
	break;
	case "Replicate":
	$query = "UPDATE project_replicates SET replicate_name=:newname WHERE project_id=:project_id AND replicate_id=:id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName, ':id' => $id);
	break;
	default:
	return;
	break;
}
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

//If set update the name in the file entry.
if($nodeType==="Set")
{
	$query = "UPDATE project_files SET set_name=:newname WHERE project_id=:project_id AND set_id=:id";
	$query_params =array(':newname' => $newName, ':project_id'=> $projectID, ':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}


//If project or condition query the file entry, update the names in the quant entry.
if ($nodeType==="Condition")
{
	//Get file id
	$query = "SELECT file_id FROM project_conditions WHERE condition_id=:id";
	$query_params = array(':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$file_id = $row['file_id'];

	//Get quant information from project_files table
	$query = "SELECT quant FROM project_files WHERE file_id=:file_id AND project_id=:project_id";
	$query_params = array(':file_id' => $file_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$quant = $row['quant'];
	$quantObj = json_decode($quant, true);
	$quant2 = array();
	foreach ($quantObj as $entry) {
		if ($entry['condName']===$currentName)
		{
			$entry['condName']=$newName;
		}
		array_push($quant2, $entry);
	}
	$quant = json_encode($quant2);

	$query = "UPDATE project_files SET quant=:quant WHERE file_id=:file_id AND project_id=:project_id";
	$query_params = array(':quant' => $quant, ':file_id' => $file_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

if ($nodeType==="Replicate")
{
	//Get file id
	$query = "SELECT file_id FROM project_replicates WHERE replicate_id=:id";
	$query_params = array(':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$file_id = $row['file_id'];

	//Get quant information from project_files table
	$query = "SELECT quant FROM project_files WHERE file_id=:file_id AND project_id=:project_id";
	$query_params = array(':file_id' => $file_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$quant = $row['quant'];
	$quantObj = json_decode($quant, true);
	$quant2 = array();
	foreach ($quantObj as $entry) {
		if ($entry['repName']===$currentName)
		{
			$entry['repName']=$newName;
		}
		array_push($quant2, $entry);
	}
	$quant = json_encode($quant2);

	$query = "UPDATE project_files SET quant=:quant WHERE file_id=:file_id AND project_id=:project_id";
	$query_params = array(':quant' => $quant, ':file_id' => $file_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}


$data = array("result"=>true, "message"=> "Successful name update! The " .$nodeType . " named '" . $currentName . "' was changed to '" . $newName . "'.");
echo(json_encode($data));
