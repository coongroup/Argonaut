<?php
require("config.php");
//if (empty($_SESSION['user'])) {
//    header("Location: index.html");
 //   die("Redirecting to index.html");
//}

$c1 = $_POST['c1'];
$c2 = $_POST['c2'];

$query = "SELECT a.fold_change_control_norm as fc1, a.p_value_control_norm as p1, b.fold_change_control_norm as fc2, b.p_value_control_norm as p2, a.unique_identifier_id, a.fdr_p_value_control_norm AS p1_fdr, a.bonferroni_p_value_control_norm AS p1_bonferroni, b.fdr_p_value_control_norm AS p2_fdr, b.bonferroni_p_value_control_norm AS p2_bonferroni FROM data_descriptive_statistics AS a INNER JOIN data_descriptive_statistics AS b WHERE a.unique_identifier_id=b.unique_identifier_id  AND a.condition_id=:c1 AND b.condition_id=:c2";
$query_params = array(':c1' => $c1, ':c2' => $c2);
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
