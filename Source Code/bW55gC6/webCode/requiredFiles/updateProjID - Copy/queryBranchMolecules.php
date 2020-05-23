<?php
require("config.php");
//if (empty($_SESSION['user'])) {
//    header("Location: index.html");
//    die("Redirecting to index.html");
//}

$branch = $_POST['bi'];
$projectID=-1;
$query = "SELECT DISTINCT (data_descriptive_statistics.unique_identifier_id), data_unique_identifiers.unique_identifier_text FROM data_descriptive_statistics JOIN data_unique_identifiers ON data_descriptive_statistics.unique_identifier_id=data_unique_identifiers.unique_identifier_id WHERE data_descriptive_statistics.branch_id=:branch";
$query_params = array(':branch' => $branch);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		array_push($data, $entry);
	}
}
echo(json_encode($data));