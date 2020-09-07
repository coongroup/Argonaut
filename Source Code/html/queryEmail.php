<?php

require("config.php");

//if(empty($_SESSION['user']))
{
//    header("Location: index.html");
//    die("Redirecting to index.html");
}

$email = $_POST['em'];
$query = "SELECT email FROM users WHERE email=:email";
$query_params = array(':email' => $email);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
$outVal="false";
if ($row)
{
$outVal = "true";
}

echo($outVal);