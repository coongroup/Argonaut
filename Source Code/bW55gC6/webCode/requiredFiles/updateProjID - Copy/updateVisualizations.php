<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant


$projectID=-1;
$volcano = $_POST['v'];
$bar = $_POST['b'];
$outlier = $_POST['o'];
$scatter = $_POST['s'];
$pcaRep = $_POST['pcar'];
$pcaCond = $_POST['pcac'];

$time = date("Y-m-d H:i:s");

//Return an array indicating success/failure, and a text statement.
$data = array();

$query = "SELECT project_name FROM projects WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	$data = array("result"=>false, "message"=>"Unable to access the central database. Please try again in a few minutes.");
	echo(json_encode($data));
	return;
}
$project_name=$row['project_name'];

//Confirm edit abilities (permission_level 2 or 3)
$query = "SELECT * FROM project_permissions WHERE user_id=:user AND project_id=:project_id AND permission_level>=2";
$query_params = array(':user' => $_SESSION['user'], ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	$data = array("result"=>false, "message"=>"You do not have permission to update Log2 transformation settings.");
	echo(json_encode($data));
	return;
}

if (($volcano==="true" && $volcano==="false") || ($bar==="true" && $bar==="false") || ($outlier==="true" && $outlier==="false") 
	|| ($scatter==="true" && $scatter==="false") || ($pcaRep==="true" && $pcaRep==="false") || ($pcaCond==="true" && $pcaCond==="false"))
{
	$data = array("result"=>false, "message"=>"Visualization inputs are invalid!");
	echo(json_encode($data));
	return;
}

$query="UPDATE project_data_visualizations SET visualization_on=:on WHERE project_id=:project_id AND visualization_id=:id";
$query_params = array(':on' => $volcano===true || $volcano==="true" ? 1 : 0, ':project_id' => $projectID, ':id' => 'volcano');
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query="UPDATE project_data_visualizations SET visualization_on=:on WHERE project_id=:project_id AND visualization_id=:id";
$query_params = array(':on' => $bar===true || $bar==="true" ? 1 : 0, ':project_id' => $projectID, ':id' => 'bar');
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query="UPDATE project_data_visualizations SET visualization_on=:on WHERE project_id=:project_id AND visualization_id=:id";
$query_params = array(':on' => $outlier===true || $outlier==="true" ? 1 : 0, ':project_id' => $projectID, ':id' => 'outlier');
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query="UPDATE project_data_visualizations SET visualization_on=:on WHERE project_id=:project_id AND visualization_id=:id";
$query_params = array(':on' => $scatter===true || $scatter==="true" ? 1 : 0, ':project_id' => $projectID, ':id' => 'scatter');
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query="UPDATE project_data_visualizations SET visualization_on=:on WHERE project_id=:project_id AND visualization_id=:id";
$query_params = array(':on' => $pcaRep===true || $pcaRep==="true" ? 1 : 0, ':project_id' => $projectID, ':id' => 'pcarep');
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query="UPDATE project_data_visualizations SET visualization_on=:on WHERE project_id=:project_id AND visualization_id=:id";
$query_params = array(':on' => $pcaCond===true || $pcaCond==="true" ? 1 : 0, ':project_id' => $projectID, ':id' => 'pcacond');
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$data = array("result"=>true, "message"=>" The " . $project_name . " web portal has been updated!");
	echo(json_encode($data));
	return;

	sleep(5);
