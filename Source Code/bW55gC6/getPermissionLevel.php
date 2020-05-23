<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

//Check for permissions which might be redundant

$projectID='bW55gC6';
$query = "SELECT permission_level FROM project_permissions WHERE project_id=:project_id AND user_id=:user_id";
$query_params = array(':project_id'=> $projectID, ':user_id'=>$_SESSION['user']);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
$data = array();
if (!$row)
{
	echo (0);
	return;
}
echo($row['permission_level']);
return;
