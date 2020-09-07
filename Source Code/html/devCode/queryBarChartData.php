<?php
require("config.php");
//if (empty($_SESSION['user'])) {
 //   header("Location: index.html");
//    die("Redirecting to index.html");
//}

$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$id = ($_POST['i']);
$branch_id= $_POST['bi'];

$argLine = "SELECT data_descriptive_statistics.fold_change_control_norm AS fold_change, data_descriptive_statistics.p_value_control_norm AS p_value, data_descriptive_statistics.fdr_p_value_control_norm AS p_value_fdr, data_descriptive_statistics.bonferroni_p_value_control_norm AS p_value_bonferroni, data_descriptive_statistics.std_dev, data_unique_identifiers.unique_identifier_text, data_unique_identifiers.unique_identifier_id AS mol_id, project_conditions.condition_name, project_conditions.condition_id FROM data_descriptive_statistics JOIN data_unique_identifiers ON "
    . "data_unique_identifiers.unique_identifier_id=data_descriptive_statistics.unique_identifier_id JOIN project_conditions ON project_conditions.condition_id=data_descriptive_statistics.condition_id WHERE data_descriptive_statistics.unique_identifier_id='" . $id . "' AND data_descriptive_statistics.branch_id='" . $branch_id . "'";

$query = mysql_query($argLine);

if (!$query) {
    echo mysql_error();
    die;
}

$data = array();

while ($rows = mysql_fetch_array($query)) {
    $data[] = $rows;
}

$t = json_encode($data);

echo($t);