<?php
require("config.php");
//if (empty($_SESSION['user'])) {
//    header("Location: index.html");
//    die("Redirecting to index.html");
//}

$branch_id = $_POST['bi'];
$search_text = $_POST['s'];
$search_text = strtolower($search_text);


$projectID=-1;
$query = "SELECT DISTINCT (data_descriptive_statistics.unique_identifier_id) AS molecule_id, data_unique_identifiers.unique_identifier_text AS name FROM data_descriptive_statistics JOIN data_unique_identifiers ON data_descriptive_statistics.unique_identifier_id = data_unique_identifiers.unique_identifier_id WHERE "
 . "data_descriptive_statistics.branch_id=:branch_id AND LOWER(data_unique_identifiers.unique_identifier_text) LIKE :search_text ORDER BY name";
$query_params = array(':branch_id' => $branch_id, ':search_text' => '%' . $search_text . '%');
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
