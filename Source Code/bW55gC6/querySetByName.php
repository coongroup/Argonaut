<?php

require("config.php");

//if(empty($_SESSION['user']))
{
//    header("Location: index.html");
//    die("Redirecting to index.html");
}

$branch_id = $_POST['bi'];
$set_name = $_POST['sn'];

$query = "SELECT * FROM project_sets WHERE set_name=:set_name AND project_id=:project_id AND branch_id=:branch_id";
$query_params = array(':set_name' => $set_name, ':project_id' => $projectID, ':branch_id' => $branch_id);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$outVal = "false";
if($row)
{
	$outVal = "true";
}

echo($outVal);
