<?php

require("config.php");

if(empty($_SESSION['user']))
{
    header("Location: index.html");
   die("Redirecting to index.html");
}

$invite_code = $_POST['ic'];
$time =  date("Y-m-d H:i:s");

sleep(1);

//need to update project_invitations
$query = "SELECT a.permission_level, a.project_id, a.invitation_accepted, b.project_name FROM project_invitations AS a JOIN projects AS b ON a.project_id=b.project_id WHERE a.unique_code=:code";
$query_params = array(':code' => $invite_code);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

$projectID = $row['project_id'];
$permission_level = $row['permission_level'];
$invitation_accepted = $row['invitation_accepted'];
$project_name = $row['project_name'];

if($invitation_accepted==="1")
{
	$data = array("result"=>false, "message"=>"The following code has already been used.");
	echo(json_encode($data));
	return;
}

$query = "SELECT permission_level FROM project_permissions WHERE project_id=:project_id AND user_id=:user_id";
$query_params = array(':project_id' => $projectID, ':user_id' => $_SESSION['user']);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

if($row)
{
	$data = array("result"=>false, "message"=>"You already have access to the '" . $project_name ."' web portal.");
	echo(json_encode($data));
	return;
}

$query = "UPDATE project_invitations SET accepting_user_id=:user_id, invitation_accepted=:invitation_accepted, invitation_accept_time=:time WHERE unique_code=:code";
$query_params = array(':user_id'=>$_SESSION['user'], ':invitation_accepted'=>1, ':time' => $time, ':code' => $invite_code);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "INSERT INTO project_permissions (project_id, user_id, permission_level) VALUES (:project_id, :user_id, :permission_level)";
$query_params = array(':user_id'=>$_SESSION['user'], ':project_id'=>$projectID, ':permission_level' => $permission_level);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$message = $permission_level==="2" || $permission_level==="3" ? "You now have access to the '" . $project_name . "' web portal. You can access the web portal by selecting it from the dropdown project list and clicking 'View Project Website.' Happy exploring!" : "You now have access to the '" . $project_name . "' web portal. You can access the web portal by selecting it from the dropdown project list and clicking 'View Project Website.' You can also edit the underlying data and visualizations by clicking the 'Edit Project' button. Happy exploring!";

$data = array("result"=>true, "message"=>$message);
	echo(json_encode($data));
	return;
//need to add to project_permissions