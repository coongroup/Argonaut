<?php

require("config.php");

//if(empty($_SESSION['user']))
{
//    header("Location: index.html");
//    die("Redirecting to index.html");
}

$username = $_POST['un'];
$query = "SELECT username FROM users WHERE username=:username";
$query_params = array(':username' => $username);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

if($row)
{
	echo("true");
	return;
}

echo("false");
	return;
