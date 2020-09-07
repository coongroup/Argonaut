<?php
require("config.php");
if(empty($_SESSION['user']))
{
    header("Location: index.html");
    die("Redirecting to index.html");
}

$projectName = $_POST['pn'];

$query = "SELECT * FROM projects WHERE creator_user_id=:user_id AND project_name=:project_name";
$query_params = array(':user_id' => $_SESSION['user'], ':project_name' => $projectName);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

$outVal = "true";
if($row)
{
	$outVal = "false";
}
echo($outVal); 