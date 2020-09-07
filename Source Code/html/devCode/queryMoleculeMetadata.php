<?php

require("config.php");

//if(empty($_SESSION['user']))

{

    //header("Location: index.html");

    //die("Redirecting to index.html");

}



$server = mysql_connect($host, $username, $password);

mysql_select_db($dbname);



$projectID=-1;
$val1 = $_POST['v1'];
$val2 = $_POST['v2'];


$argLine = "SELECT * FROM data_feature_metadata WHERE project_id='" . $projectID . "'' ORDER BY unique_identifier_id LIMIT " . $val1 . "," . $val2;


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


