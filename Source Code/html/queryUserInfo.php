<?php
require("config.php");
if (empty($_SESSION['user'])) {
    header("Location: index.html");
    die("Redirecting to index.html");
}

$query = 'SELECT first_name FROM users WHERE id=:username';
$query_params = array(':username'=>$_SESSION['user']);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

if (!$row) {
    die;
}

$data = array();

foreach ($row as $entry) {
	array_push($data, $entry);
}

echo($data[0]["first_name"]);