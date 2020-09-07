<?php

require("config.php");
//if(empty($_SESSION['user']))
{
  //  header("Location: index.html");
   // die("Redirecting to index.html");
}

$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$branchID = ($_POST['bi']);

$argLine = "SELECT project_data_summary.*, project_branches.branch_name, project_branches.branch_id FROM project_data_summary JOIN project_branches on project_branches.branch_id=project_data_summary.branch_id WHERE project_data_summary.branch_id='" . $branchID ."'";
$query1 = mysql_query($argLine);

if ( ! $query1 ) {
    echo mysql_error();
    die;
}

$data = array();

while ($rows = mysql_fetch_array($query1)) {
    $data[] = $rows;
}

echo(json_encode($data));