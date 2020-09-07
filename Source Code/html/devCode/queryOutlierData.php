<?php
require("config.php");
//if (empty($_SESSION['user'])) {
 //   header("Location: index.html");
//    die("Redirecting to index.html");
//}

$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$value = ($_POST['v']);
$branch_id= $_POST['bi'];

$argLine = "SELECT a.regulation, a.distance, a.max_regulated_condition_id, a.unique_identifier_id, b.unique_identifier_text, c.condition_name, d.fold_change_control_norm, d.p_value_control_norm, d.fdr_p_value_control_norm, d.bonferroni_p_value_control_norm FROM data_outlier_analysis AS a JOIN data_unique_identifiers AS b ON a.unique_identifier_id=b.unique_identifier_id JOIN project_conditions AS c ON c.condition_id=a.max_regulated_condition_id JOIN data_descriptive_statistics AS d ON d.condition_id=a.max_regulated_condition_id AND d.unique_identifier_id=a.unique_identifier_id WHERE a.branch_id='". $branch_id . "' AND a.algorithm='" . $value . "' ORDER BY a.distance DESC LIMIT 5000";
$query = mysql_query($argLine);

if (!$query) {
    echo mysql_error();
    die;
}
$count = 0;
$data = array();

while ($rows = mysql_fetch_array($query)) {
    $data[] = $rows;
}

$t = json_encode($data);

echo($t);