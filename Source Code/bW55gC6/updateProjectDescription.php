<?php

require("config.php");

if(empty($_SESSION['user']))
{
//    header("Location: index.html");
	die();
}

$newDescription = $_POST['n'];

$time = date("Y-m-d H:i:s");

//Return an array indicating success/failure, and a text statement.
$data = array();

$query = "SELECT project_name, project_description FROM projects WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	$data = array("result"=>false, "message"=>"Unable to access the central database. Please try again in a few minutes.");
	echo(json_encode($data));
	return;
}
$project_name=$row['project_name'];
$project_description=$row['project_description'];

//Confirm edit abilities (permission_level 2 or 3)
$query = "SELECT * FROM project_permissions WHERE user_id=:user AND project_id=:project_id AND permission_level>=2";
$query_params = array(':user' => $_SESSION['user'], ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	$data = array("result"=>false, "message"=>"You do not have permission to update this project's description.");
	echo(json_encode($data));
	return;
}

$query = "UPDATE projects SET project_description=:description WHERE project_id=:project_id";
$query_params = array(':description' => $newDescription, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$project_description = $newDescription;

$control_description = "Updated project description for '" . $project_name . "'";
$query = "INSERT INTO project_activity (project_id, activity, time, description) VALUES (:project_id, :activity, :time, :description)";
$query_params = array(':project_id' => $projectID, ':activity' => 'DESCRIPTION UPDATE', ':time' => $time, ':description' => $control_description);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$volcano = false;
$bar = false;
$outlier = false;
$scatter = false;
$pcaRep = false;
$pcaCond = false;

$query = "SELECT * FROM project_data_visualizations WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach ($row as $entry) {
	switch($entry['visualization_id'])
	{
		case "volcano": $entry['visualization_on']==="1" || $entry['visualization_on']===1 ? $volcano = true : $volcano = false; break;
		case "bar":  $entry['visualization_on']==="1" || $entry['visualization_on']===1 ? $bar = true : $bar = false; break;
		case "outlier":  $entry['visualization_on']==="1" || $entry['visualization_on']===1 ? $outlier = true : $outlier = false; break;
		case "scatter" :  $entry['visualization_on']==="1" || $entry['visualization_on']===1 ? $scatter = true : $scatter = false; break;
		case "pcarep" :  $entry['visualization_on']==="1" || $entry['visualization_on']===1 ? $pcaRep = true : $pcaRep = false; break;
		case "pcacond":  $entry['visualization_on']==="1" || $entry['visualization_on']===1 ? $pcaCond = true : $pcaCond = false; break;
	}
}


$file_location="main.php";

$file = fopen($file_location, "w");
AddToFile($file, "webCode/required_1.txt", $project_name, $project_description);
$outlier===true||$outlier==="true" ? AddToFile($file, "webCode/outlier_1.txt", $project_name, $project_description) : null;
$volcano===true||$volcano==="true" ? AddToFile($file, "webCode/volcano_1.txt", $project_name, $project_description) : null;
$bar===true||$bar==="true" ? AddToFile($file, "webCode/bar_1.txt", $project_name, $project_description) : null;
$scatter===true||$scatter==="true" ? AddToFile($file, "webCode/scatter_1.txt", $project_name, $project_description) : null;
$pcaCond===true||$pcaCond==="true" ? AddToFile($file, "webCode/pcacond_1.txt", $project_name, $project_description) : null;
$pcaRep===true||$pcaRep==="true" ? AddToFile($file, "webCode/pcarep_1.txt", $project_name, $project_description) : null;
AddToFile($file, "webCode/required_2.txt", $project_name, $project_description);
$outlier===true||$outlier==="true" ? AddToFile($file, "webCode/outlier_2.txt", $project_name, $project_description) : null;
$volcano===true||$volcano==="true" ? AddToFile($file, "webCode/volcano_2.txt", $project_name, $project_description) : null;
$bar===true||$bar==="true" ? AddToFile($file, "webCode/bar_2.txt", $project_name, $project_description) : null;
$scatter===true||$scatter==="true" ? AddToFile($file, "webCode/scatter_2.txt", $project_name, $project_description) : null;
$pcaCond===true||$pcaCond==="true" ? AddToFile($file, "webCode/pcacond_2.txt", $project_name, $project_description) : null;
$pcaRep===true||$pcaRep==="true" ? AddToFile($file, "webCode/pcarep_2.txt", $project_name, $project_description) : null;
AddToFile($file, "webCode/required_3.txt", $project_name, $project_description);
fclose($file);


$data = array("result"=>true, "message"=>" The description for '" . $project_name . "' has been updated!");
	echo(json_encode($data));
	return;

function AddToFile($file, $append_file, $project_name, $project_description)
{
	$other_file = fopen($append_file, "r");
	while(! feof($other_file))
	{
		$currLine = fgets($other_file);
		strpos($currLine, 'PROJECTNAME')!==false ? $currLine = str_replace('PROJECTNAME', $project_name, $currLine): null;
		strpos($currLine, 'PROJECTDESCRIPTION')!==false ?  $currLine = str_replace('PROJECTDESCRIPTION', $project_description, $currLine): null;
		fwrite($file, $currLine);
	}
	fclose($other_file);
}
