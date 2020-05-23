<?php
require("config.php");

if(empty($_SESSION['user']))

{

   header("Location: index.html");
    die("Redirecting to index.html");

}

$totalMeasurements = 0;
$uniqueMolecules = 0;
$filesUploaded = 0;
$invitedCollaborators = 0;

//query total number of measurements,
$queryText = "SELECT a.quant_measurement_count FROM project_data_summary AS a RIGHT JOIN project_branches AS b ON a.branch_id=b.branch_id WHERE a.project_id=:project_id";
$queryParams = array(':project_id' => $projectID);
$stmt = $db->prepare($queryText); 
$result = $stmt->execute($queryParams); 
$row = $stmt->fetchAll();

foreach ($row as $entry) {
 	$totalMeasurements+= (int)$entry['quant_measurement_count'];
 } 

//query total unique identifiers
$queryText = "SELECT COUNT(project_id) FROM data_unique_identifiers WHERE project_id=:project_id";
$stmt = $db->prepare($queryText); 
$result = $stmt->execute($queryParams); 
$row = $stmt->fetch(); 
if($row){
$uniqueMolecules = $row['COUNT(project_id)'];
}

//query files uploaded
$queryText = "SELECT COUNT(a.project_id) FROM project_sets AS a JOIN project_files AS b ON a.file_id=b.file_id WHERE a.project_id=:project_id";
$stmt = $db->prepare($queryText); 
$result = $stmt->execute($queryParams); 
$row = $stmt->fetch(); 
if($row){
$filesUploaded = $row['COUNT(a.project_id)'];
}

//query invited collaborators
$queryText = "SELECT COUNT(project_id) FROM project_permissions WHERE project_id=:project_id";
$stmt = $db->prepare($queryText); 
$result = $stmt->execute($queryParams); 
$row = $stmt->fetch(); 
if($row){
$invitedCollaborators = $row['COUNT(project_id)']-1;
}


$returnArray = array("uniqueMolecules" => $uniqueMolecules, "totalMeasurements" => $totalMeasurements, "filesUploaded" => $filesUploaded, "invitedCollaborators" => $invitedCollaborators);
echo(json_encode($returnArray));
