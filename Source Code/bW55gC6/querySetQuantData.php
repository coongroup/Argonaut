<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

$set_id = $_POST['si'];

$query = "SELECT a.header_text AS header, a.condition_name AS cond, a.replicate_name AS rep, a.is_control AS control FROM project_file_headers AS a JOIN project_sets AS b ON a.file_id=b.file_id WHERE b.set_id=:set_id AND a.is_quant_data=1 AND b.project_id=:project_id";
$query_params = array(':project_id' => $projectID, ':set_id' => $set_id);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
foreach ($row as $entry) {
	array_push($data, $entry);
}
echo(json_encode($data));
