<?php
require("config.php");
//if (empty($_SESSION['user'])) {
//    header("Location: index.html");
//    die("Redirecting to index.html");
//}
$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$projectID=-1;
$branchID = $_POST['bi'];
$isControlNorm = 1;
$c1 = $_POST['c1'];
$c2 = $_POST['c2'];

$argLine = "SELECT a.scaled_vector as pc_x_vector, a.variance_fraction as pc_x_fraction, b.scaled_vector as pc_y_vector, b.variance_fraction as pc_y_fraction, c.condition_name FROM data_pca_condition as a INNER JOIN data_pca_condition as b ON a.condition_id=b.condition_id AND a.is_control_norm=b.is_control_norm JOIN project_conditions AS c ON c.condition_id=a.condition_id WHERE a.branch_id='" . $branchID . "' AND a.component_number=" . $c1 . " AND b.component_number=" . $c2 . " AND a.is_control_norm=" . $isControlNorm;
$query = mysql_query($argLine);
if ( ! $query ) {
    echo mysql_error();
    die;
}
$data = array();
while ($rows = mysql_fetch_array($query)) {
    $data[] = $rows;
}

echo(json_encode($data));