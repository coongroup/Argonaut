<?php
require("config.php");
if (empty($_SESSION['user'])) {
    header("Location: index.html");
    die("Redirecting to index.html");
}

$projectID=-1;
$query = "SELECT activity, time AS t, description FROM project_activity WHERE project_id=:project_id ORDER BY time DESC LIMIT 4";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();


$data = array();
foreach ($row as $entry) {
	array_push($data, $entry);
}

$t = json_encode($data);

echo($t);
