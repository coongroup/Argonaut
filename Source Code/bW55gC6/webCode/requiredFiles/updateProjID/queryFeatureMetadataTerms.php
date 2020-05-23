<?php

require("config.php");
$projectID=-1;
$query = "SELECT DISTINCT(feature_metadata_name) FROM data_feature_metadata WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		array_push($data, $entry['feature_metadata_name']);
	}
}
echo(json_encode($data));

?>
