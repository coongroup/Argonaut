<?php

require("config.php");
if(empty($_SESSION['user']))
{
    //header("Location: index.html");
    die("Redirecting to index.html");
}

$projectID=-1;
$query = "SELECT project_name AS name, project_description AS description FROM projects WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
$data = array();
if($row)
{
	$data = array("name" => $row['name'], 'description' => $row['description']);
}
echo(json_encode($data));

