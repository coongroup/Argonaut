<?php

require("config.php");

//if (empty($_SESSION['user'])) {

   // header("Location: index.html");

  //  die("Redirecting to index.html");

//}


$descriptor = $_POST['d'];
$query = "SELECT * FROM data_query_terms WHERE LOWER(query_term_text) LIKE :descriptor AND project_id=:project_id ORDER BY query_term_text";
$query_params = array(':project_id' => $projectID, ':descriptor' => '%' . $descriptor . '%');
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
if($row)
{
	foreach ($row as $entry) {
		array_push($data, $entry);
	}
}
echo(json_encode($data));

?>
