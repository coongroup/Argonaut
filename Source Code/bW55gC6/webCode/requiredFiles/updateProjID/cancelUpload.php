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

try{
	$query = "DELETE FROM project_temporary_files WHERE uploader_user_id=:user_id AND project_id=:project_id";
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}
catch (PDOException $ex)
{
	die("Failed to run query: " . $ex->getMessage());
}


$query = "DELETE FROM project_temporary_file_headers WHERE uploader_user_id=:user_id AND project_id=:project_id";
try
{
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params); }
	catch (PDOException $ex) {
		die("Failed to run query: " . $ex->getMessage());
	}
