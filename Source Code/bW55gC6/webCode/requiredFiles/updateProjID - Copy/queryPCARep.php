<?php
require("config.php");
//if (empty($_SESSION['user'])) {
//    header("Location: index.html");
//    die("Redirecting to index.html");
//}

$branchID = $_POST['bi'];
$c1 = $_POST['c1'];
$c2 = $_POST['c2'];


$projectID=-1;
$query = "SELECT a.scaled_vector as pc_x_vector, a.variance_fraction as pc_x_fraction, b.scaled_vector as pc_y_vector, b.variance_fraction as pc_y_fraction, c.replicate_name, c.replicate_id AS id, d.condition_name AS condition_name, c.condition_id, e.set_name FROM data_pca_replicate as a INNER JOIN data_pca_replicate as b ON a.replicate_id=b.replicate_id AND a.is_control_norm=b.is_control_norm JOIN project_replicates AS c ON c.replicate_id=a.replicate_id JOIN project_conditions AS d ON d.condition_id=c.condition_id JOIN project_sets AS e ON e.set_id=d.set_id WHERE a.branch_id=:branch_id AND a.component_number=:c1 AND b.component_number=:c2 AND a.project_id=:project_id";
$query_params = array(':branch_id' => $branchID, ':c1' => $c1, ':c2' => $c2, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		$entry['replicate_name'] = $entry['replicate_name'] . " (" . $entry['set_name'] . ")";
		$entry['condition_name'] = $entry['condition_name'] . " (" . $entry['set_name'] . ")";
		array_push($data, $entry);
	}
}
echo(json_encode($data));