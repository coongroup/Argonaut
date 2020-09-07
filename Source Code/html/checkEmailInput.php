<?php
require("config.php");

$server = mysql_connect($host, $username, $password);
mysql_select_db($dbname);

$email = $_POST['e'];
//$email = "nickwiecien2@gmail.com";

$query = "SELECT * FROM users WHERE email=:email";
$query_params = array(':email' => $email);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

$outVal = "false";
if($row)
{
	$outVal = "true";
}
echo($outVal); 