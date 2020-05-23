<?php
require("config.php");
//if (empty($_SESSION['user'])) {
 //   header("Location: index.html");
//    die("Redirecting to index.html");
//}

$value = ($_POST['v']);
$branch_id= $_POST['bi'];

$projectID=-1;
$query = "SELECT a.regulation, a.distance, a.max_regulated_condition_id, a.unique_identifier_id, b.unique_identifier_text, c.condition_name, d.fold_change_control_norm, d.p_value_control_norm, d.fdr_p_value_control_norm, d.bonferroni_p_value_control_norm, e.set_name FROM data_outlier_analysis AS a JOIN data_unique_identifiers AS b ON a.unique_identifier_id=b.unique_identifier_id JOIN project_conditions AS c ON c.condition_id=a.max_regulated_condition_id JOIN data_descriptive_statistics AS d ON d.condition_id=a.max_regulated_condition_id AND d.unique_identifier_id=a.unique_identifier_id JOIN project_sets AS e ON e.set_id=d.set_id WHERE a.branch_id=:branch AND a.algorithm=:algorithm ORDER BY a.distance DESC LIMIT 5000";
$query_params = array(':branch' => $branch_id, ':algorithm' => $value);
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