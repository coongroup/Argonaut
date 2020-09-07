<?php
require("config.php");
if (empty($_SESSION['user'])) {
    header("Location: index.html");
    die("Redirecting to index.html");
}

$query = 'SELECT privleged_user FROM users WHERE id=:username';
$query_params = array(':username'=>$_SESSION['user']);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();

if (!$row) {
    die;
}

$data = array();

foreach ($row as $entry) {
	array_push($data, $entry);
}

echo($row['privleged_user']);