<?php
require("../config.php");

if(empty($_SESSION['user']))
{
    //header("Location: index.html");
    die("Redirecting to index.html");
}

$stmt = $db->prepare("SELECT project_name, project_description FROM projects WHERE project_id = :project_id");
$result = $stmt->execute(['project_id' => $projectID]);
$row = $stmt->fetch();

if($row)
{
	$data = array("projectName" => $row['project_name'], 'projectDescription' => $row['project_description']);
}

echo(json_encode($data));
// echo(json_encode($db));
?>