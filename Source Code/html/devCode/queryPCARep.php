<?php
require("config.php");
if (empty($_SESSION['user'])) {
    header("Location: index.html");
    die("Redirecting to index.html");
}
$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$projectID=-1;
$branchID = $_POST['bi'];
$c1 = $_POST['c1'];
$c2 = $_POST['c2'];

$argLine = "SELECT a.scaled_vector as pc_x_vector, a.variance_fraction as pc_x_fraction, b.scaled_vector as pc_y_vector, b.variance_fraction as pc_y_fraction, c.replicate_name, c.replicate_id AS id, d.condition_name AS condition_name, c.condition_id FROM data_pca_replicate as a INNER JOIN data_pca_replicate as b ON a.replicate_id=b.replicate_id AND a.is_control_norm=b.is_control_norm JOIN project_replicates AS c ON c.replicate_id=a.replicate_id JOIN project_conditions AS d ON d.condition_id=c.condition_id WHERE a.branch_id='" . $branchID . "' AND a.component_number=" . $c1 . " AND b.component_number=" . $c2 . " AND a.project_id=" . $projectID . " AND a.is_control_norm=1 AND a.is_mean_norm=0";
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
