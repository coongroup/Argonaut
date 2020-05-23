<?php
require("config.php");

if(empty($_SESSION['user']))

{

   header("Location: index.html");
    die("Redirecting to index.html");

}

$projectID=-1;

$totalMeasurements = 0;
$uniqueMolecules = 0;
$filesUploaded = 0;
$invitedCollaborators = 0;


//query total number of measurements,
$queryText = "SELECT COUNT(replicate_id) FROM data_replicate_data WHERE project_id=:project_id AND use_data=1";
$queryParams = array(':project_id' => $projectID);
$stmt = $db->prepare($queryText); 
$result = $stmt->execute($queryParams); 
$row = $stmt->fetch(); 
if($row){
$totalMeasurements = $row['COUNT(replicate_id)'];
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
$queryText = "SELECT COUNT(project_id) FROM project_files WHERE project_id=:project_id";
$stmt = $db->prepare($queryText); 
$result = $stmt->execute($queryParams); 
$row = $stmt->fetch(); 
if($row){
$filesUploaded = $row['COUNT(project_id)'];
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
