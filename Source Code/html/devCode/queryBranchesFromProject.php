<?php

require("config.php");

//if(empty($_SESSION['user']))
{
//    header("Location: index.html");
//    die("Redirecting to index.html");
}

$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$projectID=-1;

$argLine = "SELECT project_branches.branch_name, project_branches.branch_id FROM  project_branches WHERE project_branches.project_id='" . $projectID ."'";
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