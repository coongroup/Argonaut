<?php

require("config.php");

if(empty($_SESSION['user']))
{
   // header("Location: index.html");
    //die("Redirecting to index.html");
}

$branchID = ($_POST['bi']);

$query = "SELECT project_conditions.*, project_sets.set_name FROM project_conditions JOIN project_sets ON project_conditions.set_id=project_sets.set_id WHERE project_conditions.project_id=:project_id AND project_conditions.branch_id=:branch_id AND is_control=0";
$query_params = array(':project_id' => $projectID, ':branch_id' => $branchID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		$output = array();
		$output['condition_name'] = $entry['condition_name'] . " (" . $entry['set_name'] . ")";
		$output['condition_id'] = $entry['condition_id'];
		array_push($data, $output);
	}
}
echo(json_encode($data));


?>
