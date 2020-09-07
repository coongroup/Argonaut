<?php

require("config.php");

if(empty($_SESSION['user']))

{

   // header("Location: index.html");

    //die("Redirecting to index.html");

}



$server = mysql_connect($host, $username, $password);

mysql_select_db($dbname);



$projectID=-1;
$branchID = ($_POST['bi']);



$argLine = "SELECT * FROM project_conditions WHERE project_id='" . $projectID . "'' AND branch_id='" . $branchID . "' AND is_control=0";



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
