<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant

$projectID=-1;
$type = $_POST['t'];
$name = strtolower($_POST['n']);
$id=$_POST['i'];

$query = "";
$set_id = "";
$branch_id="";

if($type==="Condition")
{
  //get set
	$query="SELECT set_id FROM project_conditions WHERE condition_id=:id";
	$query_params=array(':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		echo("false");
		return;
	}
	$set_id=$row['set_id'];
}

if($type==="Replicate")
{
  //get set
	$query="SELECT set_id FROM project_replicates WHERE replicate_id=:id";
	$query_params=array(':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		echo("false");
		return;
	}
	$set_id=$row['set_id'];
}

if($type==="Set")
{
	//get branch
	$query="SELECT branch_id FROM project_sets WHERE set_id=:id";
	$query_params=array(':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		echo("false");
		return;
	}
	$branch_id=$row['branch_id'];
}

switch ($type)
{
	case "Project":
	$query = "SELECT * FROM projects WHERE creator_user_id=:user_id AND LOWER(project_name)=:name";
	$query_params = array(':user_id' => $_SESSION['user'], ':name' => $name);
	break;

	case "Branch":
	$query = "SELECT * FROM project_branches WHERE project_id=:project_id AND LOWER(branch_name)=:name";
	$query_params = array(':project_id' => $projectID, ':name' => $name);
	break;

	case "Set":
	$query = "SELECT * FROM project_sets WHERE branch_id=:branch_id AND LOWER(set_name)=:name";
	$query_params = array(':branch_id' => $branch_id, ':name' => $name);
	break;

	case "Condition":
	$query = "SELECT * FROM project_conditions WHERE set_id=:set_id AND LOWER(condition_name)=:name";
	$query_params = array(':set_id' => $set_id, ':name' => $name);
	break;

	case "Replicate":
	$query = "SELECT * FROM project_replicates WHERE set_id=:set_id AND LOWER(replicate_name)=:name";
	$query_params = array(':set_id' => $set_id, ':name' => $name);
	break;

	default:
	echo("false");
	return;
	break;
}

$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	echo("false");
	return;
}

echo("true");
