<?php
require("config.php");
//if (empty($_SESSION['user'])) {
 //   header("Location: index.html");
//    die("Redirecting to index.html");
//}

$id = ($_POST['i']);
$branch_id= $_POST['bi'];

$projectID=-1;
$query = "SELECT data_descriptive_statistics.fold_change_control_norm AS fold_change, data_descriptive_statistics.p_value_control_norm AS p_value, data_descriptive_statistics.fdr_p_value_control_norm AS p_value_fdr, data_descriptive_statistics.bonferroni_p_value_control_norm AS p_value_bonferroni, data_descriptive_statistics.std_dev, data_unique_identifiers.unique_identifier_text, data_unique_identifiers.unique_identifier_id AS mol_id, project_conditions.condition_name, project_conditions.condition_id, project_sets.set_name FROM data_descriptive_statistics JOIN data_unique_identifiers ON "
    . "data_unique_identifiers.unique_identifier_id=data_descriptive_statistics.unique_identifier_id JOIN project_conditions ON project_conditions.condition_id=data_descriptive_statistics.condition_id JOIN project_sets ON project_sets.set_id=project_conditions.set_id WHERE data_descriptive_statistics.unique_identifier_id=:id AND data_descriptive_statistics.branch_id=:branch_id";
$query_params = array(':branch_id' => $branch_id, ':id' => $id);
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
