<?php
require("config.php");
if(empty($_SESSION['user']))
{
    //header("Location: index.html");
    //die("Redirecting to index.html");
}

$conditionID = $_POST['ci'];

$query = "SELECT unique_identifier_id, fold_change_control_norm AS fc, p_value_control_norm AS p_value, quant_val, fdr_p_value_control_norm AS p_value_fdr, bonferroni_p_value_control_norm AS p_value_bonferroni FROM data_descriptive_statistics WHERE project_id=:project_id AND condition_id=:condition_id";
$query_params = array(':project_id' => $projectID, ':condition_id' => $conditionID);
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


?>
