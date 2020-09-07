<?php

require("config.php");

//if(empty($_SESSION['user']))
{
//    header("Location: index.html");
//    die("Redirecting to index.html");
}

$projectID=-1;
$branchName = $_POST['bn'];
$query = "SELECT * FROM  project_branches WHERE project_id=:project_id AND branch_name=:branch_name";
$query_params = array(':project_id' => $projectID, ':branch_name' => $branchName);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$outVal = "false";
if($row)
{
	$outVal = "true";
}

echo($outVal);
