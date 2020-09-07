<?php

require("config.php");

if(empty($_SESSION['user']))
{
  die();
}

$projectID=-1;
$newBranchName = $_POST['bn'];

if (empty($newBranchName))
{
	die();
}

$argLine = "SELECT 1 FROM project_branches WHERE project_id=:project_id AND branch_name=:branch_name";
$query_params = array( ':project_id' => $projectID,
	':branch_name' => $newBranchName);
$stmt = $db->prepare($argLine); 
$result = $stmt->execute($query_params); 
$row = $stmt->fetch(); 
if ($row)
{
	die();
}

$lockText = "LOCK TABLES project_max_nodes WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

$argLine = "SELECT max_branch_number FROM project_max_nodes WHERE project_id=:project_id";
$query_params = array( ':project_id' => $projectID);

$stmt = $db->prepare($argLine); 
$result = $stmt->execute($query_params); 
$branch_number = 0;
 $row = $stmt->fetch(); 
if($row)
{
$branch_number = $row['max_branch_number'];
	$branch_number++;
}
else
{
	die();
}

$argLine = "UPDATE project_max_nodes SET max_branch_number=:number WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID, ':number' => $branch_number);
$stmt = $db->prepare($argLine); 
$result = $stmt->execute($query_params); 

$lockText = "UNLOCK TABLES";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();

$branchID = $projectID . "-" . (string)$branch_number . "B";

$argLine = "INSERT INTO project_branches (branch_id, project_id, branch_number, branch_name) VALUES ( :branch_id, :project_id, :branch_number, :branch_name)";
$query_params = array( ':project_id' => $projectID,
	':branch_id' => $branchID,
	':branch_number' => $branch_number,
	':branch_name' => $newBranchName);

$stmt = $db->prepare($argLine); 
$result = $stmt->execute($query_params); 

