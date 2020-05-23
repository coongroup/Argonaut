<?php

require("config.php");

if(empty($_SESSION['user']))
{
//   header("Location: index.html");
	die();
}

$projectID='bW55gC6';
$nodeType = $_POST['t'];
$newName = $_POST['n'];
$id = $_POST['i'];
$currentName = $_POST['cn'];

$project_description = "";

//Return an array indicating success/failure, and a text statement.
$data = array();

//Confirm edit abilities (permission_level 2 or 3)
$query = "SELECT * FROM project_permissions WHERE user_id=:user AND project_id=:project_id AND permission_level>=2";
$query_params = array(':user' => $_SESSION['user'], ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	$data = array("result"=>false, "message"=>"You do not have permission to rename any project data.");
	echo(json_encode($data));
	return;
}

//Confirm rename is either Project, Branch, Set, Condition, or Replicate (merged with code below).

//Confirm that existing project, branch, etc. has the appropriate name.
switch($nodeType)
{
	case "Project":
	$query = "SELECT * FROM projects WHERE project_id=:project_id AND project_name=:name";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName);
	break;
	case "Branch":
	$query = "SELECT * FROM project_branches WHERE project_id=:project_id AND branch_name=:name AND branch_id=:id";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName, ':id'=> $id);
	break;
	case "Set":
	$query = "SELECT * FROM project_sets WHERE project_id=:project_id AND set_name=:name AND set_id=:id";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName, ':id'=>$id);
	break;
	case "Condition":
	$query = "SELECT * FROM project_conditions WHERE project_id=:project_id AND condition_name=:name AND condition_id=:id";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName, ':id'=>$id);
	break;
	case "Replicate":
	$query = "SELECT * FROM project_replicates WHERE project_id=:project_id AND replicate_name=:name AND replicate_id=:id";
	$query_params = array(':project_id' => $projectID, ':name' => $currentName, ':id'=>$id);
	break;
	default:
	$data = array("result"=>false, "message"=>"Specified node type cannot be found.");
	echo(json_encode($data));
	return;
	break;
}
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
if(!$row)
{
	$data = array("result"=>false, "message"=>"No " . $nodeType . " named " . $currentName . " was found.");
	echo(json_encode($data));
	return;
}

if($nodeType==="Project")
{
	$project_description = $row['project_description'];
}

//Add an entry to project activity.
$query = "INSERT INTO project_activity (project_id, activity, time, description) VALUES (:project_id, :activity, :time, :description)";
$query_params = array(':project_id' => $projectID, ':activity' => $nodeType . " Name Update", ':time' => date("Y-m-d H:i:s"), ':description' => $nodeType . " name update. '" . $currentName . "' changed to '" . $newName . "'.");
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);


//Update the name.
switch($nodeType)
{
	case "Project":
	$query = "UPDATE projects SET project_name=:newname WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName);
	break;
	case "Branch":
	$query = "UPDATE project_branches SET branch_name=:newname WHERE project_id=:project_id AND branch_id=:id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName, ':id' => $id);
	break;
	case "Set":
	$query = "UPDATE project_sets SET set_name=:newname WHERE project_id=:project_id AND set_id=:id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName, ':id' => $id);
	break;
	case "Condition":
	$query = "UPDATE project_conditions SET condition_name=:newname WHERE project_id=:project_id AND condition_id=:id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName, ':id' => $id);
	break;
	case "Replicate":
	$query = "UPDATE project_replicates SET replicate_name=:newname WHERE project_id=:project_id AND replicate_id=:id";
	$query_params = array(':project_id' => $projectID, ':newname' => $newName, ':id' => $id);
	break;
	default:
	return;
	break;
}
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

//If set update the name in the file entry.
if($nodeType==="Set")
{
	$query = "UPDATE project_files SET set_name=:newname WHERE project_id=:project_id AND set_id=:id";
	$query_params =array(':newname' => $newName, ':project_id'=> $projectID, ':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}


//If project or condition query the file entry, update the names in the quant entry.
if ($nodeType==="Condition")
{
	//Get file id
	$query = "SELECT file_id FROM project_conditions WHERE condition_id=:id";
	$query_params = array(':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$file_id = $row['file_id'];

	//Get quant information from project_files table
	$query = "SELECT quant FROM project_files WHERE file_id=:file_id AND project_id=:project_id";
	$query_params = array(':file_id' => $file_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$quant = $row['quant'];
	$quantObj = json_decode($quant, true);
	$quant2 = array();
	foreach ($quantObj as $entry) {
		if ($entry['condName']===$currentName)
		{
			$entry['condName']=$newName;
		}
		array_push($quant2, $entry);
	}
	$quant = json_encode($quant2);

	$query = "UPDATE project_files SET quant=:quant WHERE file_id=:file_id AND project_id=:project_id";
	$query_params = array(':quant' => $quant, ':file_id' => $file_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

if ($nodeType==="Replicate")
{
	//Get file id
	$query = "SELECT file_id FROM project_replicates WHERE replicate_id=:id";
	$query_params = array(':id' => $id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$file_id = $row['file_id'];

	//Get quant information from project_files table
	$query = "SELECT quant FROM project_files WHERE file_id=:file_id AND project_id=:project_id";
	$query_params = array(':file_id' => $file_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$quant = $row['quant'];
	$quantObj = json_decode($quant, true);
	$quant2 = array();
	foreach ($quantObj as $entry) {
		if ($entry['repName']===$currentName)
		{
			$entry['repName']=$newName;
		}
		array_push($quant2, $entry);
	}
	$quant = json_encode($quant2);

	$query = "UPDATE project_files SET quant=:quant WHERE file_id=:file_id AND project_id=:project_id";
	$query_params = array(':quant' => $quant, ':file_id' => $file_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

if($nodeType==="Project")
{
	$bar = 0; $volcano = 0; $scatter = 0; $outlier=0; $pcacond = 0; $pcarep = 0;
	//need to update the project webpage
	$query = "SELECT * FROM project_data_visualizations WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	foreach ($row as $entry) {
		$viz = $entry['visualization_id'];
		switch ($viz) {
			case 'volcano':
				$volcano = $entry['visualization_on'];
				break;
			case 'outlier':
				$outlier = $entry['visualization_on'];
				break;
			case 'scatter':
				$scatter = $entry['visualization_on'];
				break;
			case 'outlier':
				$outlier = $entry['visualization_on'];
				break;
			case 'pcacond':
				$pcacond = $entry['visualization_on'];
				break;
			case 'pcarep':
				$pcarep = $entry['visualization_on'];
				break;
			
			default:
				# code...
				break;
		}
	}

	$file_location="main.php";

	$file = fopen($file_location, "w");
	AddToFile($file, "../../webCode/required_1.txt", $newName, $project_description);
	$outlier===1||$outlier==="1" ? AddToFile($file, "../../webCode/outlier_1.txt", $newName, $project_description) : null;
	$volcano===1||$volcano==="1" ? AddToFile($file, "../../webCode/volcano_1.txt", $newName, $project_description) : null;
	$bar===1||$bar==="1" ? AddToFile($file, "../../webCode/bar_1.txt", $newName, $project_description) : null;
	$scatter===1||$scatter==="1" ? AddToFile($file, "../../webCode/scatter_1.txt", $newName, $project_description) : null;
	$pcacond===1||$pcacond==="1" ? AddToFile($file, "../../webCode/pcacond_1.txt", $newName, $project_description) : null;
	$pcarep===1||$pcarep==="1" ? AddToFile($file, "../../webCode/pcarep_1.txt", $newName, $project_description) : null;
	AddToFile($file, "../../webCode/required_2.txt", $newName, $project_description);
	$outlier===1||$outlier==="1" ? AddToFile($file, "../../webCode/outlier_2.txt", $newName, $project_description) : null;
	$volcano===1||$volcano==="1" ? AddToFile($file, "../../webCode/volcano_2.txt", $newName, $project_description) : null;
	$bar===1||$bar==="1" ? AddToFile($file, "../../webCode/bar_2.txt", $newName, $project_description) : null;
	$scatter===1||$scatter==="1" ? AddToFile($file, "../../webCode/scatter_2.txt", $newName, $project_description) : null;
	$pcacond===1||$pcacond==="1" ? AddToFile($file, "../../webCode/pcacond_2.txt", $newName, $project_description) : null;
	$pcarep===1||$pcarep==="1" ? AddToFile($file, "../../webCode/pcarep_2.txt", $newName, $project_description) : null;
	AddToFile($file, "../../webCode/required_3.txt", $newName, $project_description);
	fclose($file);

	$update_files = scandir('../../webCode/requiredFiles/updateProjID');

	foreach ($update_files as $filename) {
		if(strpos($filename, '.php')!==false && strpos($filename, 'dashboard')!==false) {
			$new_path = '../../DV/' . $projectID . '/' .$filename;
			$all_lines = array();
			$file = fopen('../../webCode/requiredFiles/updateProjID/' . $filename, "r");
			while (!feof($file)) {
				$currLine = fgets($file);
				strpos($currLine, '$projectID=-1')!==false ? $currLine = "\$projectID='" . $projectID . "';" . PHP_EOL : null;
				strpos($currLine, '$scope.projectID=-1;')!==false ? $currLine = "\$scope.projectID='" . $projectID . "';" . PHP_EOL : null;
				strpos($currLine, 'PROJECTNAMEHERE')!==false ? $currLine = str_replace('PROJECTNAMEHERE', $newName, $currLine): null;
				strpos($currLine, 'PROJECTDESCRIPTIONHERE')!==false ?  $currLine = str_replace('PROJECTDESCRIPTIONHERE', $project_description, $currLine): null;
				array_push($all_lines, $currLine);
			}
			fclose($file);
			$new_file = fopen($new_path, "w");
			foreach ($all_lines as $line) {
				fwrite($new_file, $line);
			}
			fclose($new_file);
		}
	}
}

sleep(1);
$data = array("result"=>true, "message"=> "Successful name update! The " .$nodeType . " named '" . $currentName . "' was changed to '" . $newName . "'.");
echo(json_encode($data));


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
