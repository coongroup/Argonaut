<?php

require("config.php");

if(empty($_SESSION['user']))
{
   // header("Location: index.html");
    //die("Redirecting to index.html");
	die();
}

$branchID = ($_POST['bi']);
$query = "SELECT project_replicates.*, project_sets.set_name FROM project_replicates JOIN project_sets ON project_replicates.set_id=project_sets.set_id WHERE project_replicates.project_id=:project_id AND project_replicates.branch_id=:branch_id AND is_control=0";
$query_params = array(':project_id' => $projectID, ':branch_id' => $branchID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		$output = array();
		$output['replicate_name'] = $entry['replicate_name'] . " (" . $entry['set_name'] . ")";
		$output['replicate_id'] = $entry['replicate_id'];
		array_push($data, $output);
	}
}
echo(json_encode($data));


?>
