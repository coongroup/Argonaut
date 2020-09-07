<?php
require("config.php");

$projectID=-1;
//$analysisID=22;
$analysisID=$_POST['ai'];

$query = "DELETE FROM hierarchical_clustering_inputs WHERE analysis_id=:analysis_id AND project_id=:project_id";
$query_params = array(':analysis_id' => $analysisID, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "DELETE FROM hierarchical_clustering_results WHERE analysis_id=:analysis_id AND project_id=:project_id";
$query_params = array(':analysis_id' => $analysisID, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$query = "DELETE FROM hierarchical_clustering_go_analysis WHERE analysis_id=:analysis_id AND project_id=:project_id";
$query_params = array(':analysis_id' => $analysisID, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$allFiles = scandir('C:/xampp/htdocs/Projects/WebDeposition_v4/DV/' . $projectID . '/');

$goFiles = array_filter($allFiles, "fitsRequirements");

foreach ($goFiles as $entry) {
	unset($entry);
}


function fitsRequirements($var)
{
	global $analysisID;

	if (substr($var, 0, (strlen($analysisID)+1))===$analysisID."_")
	{
		if (strpos($var, '_GOEnrich')!==false)
		{
			return true;
		}
	}
	return false;
}
