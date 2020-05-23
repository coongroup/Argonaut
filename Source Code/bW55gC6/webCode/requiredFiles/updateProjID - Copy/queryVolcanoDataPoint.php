<?php

require("config.php");

//if (empty($_SESSION['user'])) {
   // header("Location: index.html");
  //  die("Redirecting to index.html");
//}


$descriptor = $_POST['d'];
$dataType = $_POST['dt'];
$projectID=-1;

if ($dataType==="Molecule identifier")
{
$query = "SELECT unique_identifier_id, unique_identifier_text FROM data_unique_identifiers WHERE LOWER(unique_identifier_text) LIKE :descriptor AND project_id=:project_id LIMIT 100";
$query_params = array(':project_id' => $projectID, ':descriptor' => '%' . $descriptor . '%');
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		array_push($data, array("unique_id" => $entry['unique_identifier_id'], "text" => $entry['unique_identifier_text']));
	}
}
echo(json_encode($data));
}
else
{
$query = "SELECT unique_identifier_id, feature_metadata_text FROM data_feature_metadata WHERE LOWER(feature_metadata_text) LIKE :descriptor AND feature_metadata_name=:name AND project_id=:project_id LIMIT 100";
$query_params = array(':project_id' => $projectID, ':descriptor' => '%' . $descriptor . '%', ':name' => $dataType);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		array_push($data, array("unique_id" => $entry['unique_identifier_id'], "text" => $entry['feature_metadata_text']));
	}
}
echo(json_encode($data));
}

?>