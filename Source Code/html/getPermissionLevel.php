<?php
require("config.php");
if(empty($_SESSION['user']))
{
    header("Location: index.html");
    die("Redirecting to index.html");
}


$projectID = $_POST['p'];

$query = "SELECT * FROM project_permissions WHERE project_id=:project_id AND user_id=:user";
$query_params = array(':project_id' => $projectID, ':user' => $_SESSION['user']);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

if(!$row)
{
	echo(0);
	return;
}
echo($row['permission_level']);
