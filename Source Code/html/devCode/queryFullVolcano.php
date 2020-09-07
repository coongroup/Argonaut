<?php
require("config.php");
if(empty($_SESSION['user']))
{
    //header("Location: index.html");
    //die("Redirecting to index.html");
}

$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$projectID=-1;
$conditionID = $_POST['ci'];

$argLine = "SELECT unique_identifier_id, fold_change_control_norm AS fc, p_value_control_norm AS p_value, quant_val, fdr_p_value_control_norm AS p_value_fdr, bonferroni_p_value_control_norm AS p_value_bonferroni FROM data_descriptive_statistics WHERE project_id='" . $projectID . "'' AND condition_id='" . $conditionID . "'";

$query = mysql_query($argLine);

if ( ! $query ) {
    echo mysql_error();
    die;
}

$data = array();
while ($rows = mysql_fetch_array($query)) {
    $data[] = $rows;
}

$t = json_encode($data);

echo($t);

?>
