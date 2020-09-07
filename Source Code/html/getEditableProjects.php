<?php
require("config.php");
if (empty($_SESSION['user'])) {
    header("Location: index.html");
    die("Redirecting to index.html");
}

$query = "SELECT projects.* FROM project_permissions JOIN projects ON projects.project_id=project_permissions.project_id WHERE 
project_permissions.permission_level>=1 AND project_permissions.user_id=:user_id";
$query_params = array(':user_id' => $_SESSION['user']);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

$data = array();
foreach ($row as $entry) {
	array_push($data, $entry);
}
usort($data, "sortFunction");
$t = json_encode($data);

echo($t);

function sortFunction( $a, $b ) {
    return strtotime($b['last_activity']) - strtotime($a['last_activity']);
}