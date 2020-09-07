<?php

require("config.php");

if(empty($_SESSION['user']))

{

    header("Location: index.html");

    die("Redirecting to index.html");

}

$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);
$projectID=-1;

$argLine = "SELECT * FROM project_conditions WHERE project_id='" . $projectID . "'";

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
