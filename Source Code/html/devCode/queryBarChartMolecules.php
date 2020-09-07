<?php
require("config.php");
//if (empty($_SESSION['user'])) {
//    header("Location: index.html");
//    die("Redirecting to index.html");
//}
$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$branch_id = $_POST['bi'];
$search_text = $_POST['s'];
$search_text = strtolower($search_text);

$argLine = "SELECT DISTINCT (data_descriptive_statistics.unique_identifier_id) AS molecule_id, data_unique_identifiers.unique_identifier_text AS name FROM data_descriptive_statistics JOIN data_unique_identifiers ON data_descriptive_statistics.unique_identifier_id = data_unique_identifiers.unique_identifier_id WHERE "
 . "data_descriptive_statistics.branch_id='"  . $branch_id . "' AND LOWER(data_unique_identifiers.unique_identifier_text) LIKE '%" . $search_text . "%' ORDER BY name";

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