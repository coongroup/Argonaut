<?php
require("config.php");
//if (empty($_SESSION['user'])) {
//    header("Location: index.html");
//    die("Redirecting to index.html");
//}

$branchID = $_POST['bi'];
$isControlNorm = 1;
$c1 = $_POST['c1'];
$c2 = $_POST['c2'];

$projectID=-1;
$query = "SELECT a.scaled_vector as pc_x_vector, a.variance_fraction as pc_x_fraction, b.scaled_vector as pc_y_vector, b.variance_fraction as pc_y_fraction, c.condition_name, d.set_name FROM data_pca_condition as a INNER JOIN data_pca_condition as b ON a.condition_id=b.condition_id AND a.is_control_norm=b.is_control_norm JOIN project_conditions AS c ON c.condition_id=a.condition_id JOIN project_sets AS d ON d.set_id=c.set_id WHERE a.branch_id=:branch_id AND a.component_number=:c1 AND b.component_number=:c2";
$query_params = array(':branch_id' => $branchID, ':c1' => $c1, ':c2' => $c2);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		$entry['condition_name'] = $entry['condition_name'] . " (" . $entry['set_name'] . ")";
		array_push($data, $entry);
	}
}
echo(json_encode($data));