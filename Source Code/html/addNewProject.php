<?php
require("config.php");
if (empty($_SESSION['user'])) {
	header("Location: index.html");
	die("Redirecting to index.html");
}

$project_name = $_POST['pn'];
$project_description = $_POST['pd'];
$perm_project = $_POST['pp'];
$lower_project_name = strtolower($project_name);
$is_perm_project=0;
$time = date("Y-m-d H:i:s");
if ($perm_project=="true")
{
	$is_perm_project=1;
}

if($_SESSION['privleged_user']!=="1" && $_SESSION['privleged_user']!==1)
{
    $data = array("result"=>false, "message"=>"You currently cannot create a new project. Please contact Nick Kwiecien to upgrade your permissions.");
	echo(json_encode($data));
	return;
}

//confirm that project name doesn't already exist
$query = "SELECT 1 FROM projects WHERE LOWER(project_name)=:project_name AND creator_user_id=:user_id";
$query_params = array(':project_name' => $lower_project_name, ':user_id' => $_SESSION['user']);
$stmt = $db->prepare($query); 
$result = $stmt->execute($query_params);
$row = $stmt->fetch(); 
if($row)
{
	$data = array("result"=>false, "message"=>"A project named " . $project_name . " already exists! Please rename your new project and try again.");
	echo(json_encode($data));
	return;
}

//find unique 7-digit code
$projectID=randomKey(7);
while (true) {
	$query = "SELECT * FROM projects WHERE project_id=:project_id";
	$query_params = array(':project_id' =>$projectID);
	$stmt = $db->prepare($query); 
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch(); 
	if ($row)
	{
		$projectID=randomKey(7);
	}
	else
	{
		break;
	}
}

//transfer all of the files first before adding project entries. You can always delete project stuff that doesn't point to a project. You don't want to have a database entry for a project that you can't add data to/edit

//create project directory in DV and server

$server_directory = "server/php/files/" . $projectID;
$dv_directory = "DV/" . $projectID;

try{
	mkdir($server_directory, 0755);
	mkdir($dv_directory, 0755);
}
catch (Exception $e) {
	$data = array("result"=>false, "message"=>"Unable to create a new project entry at this time. Please try again in a few moments.");
	echo(json_encode($data));
	return;
}

try{
//copy over required files (no update needed)
	$no_update_files = scandir('webCode/requiredFiles');

	foreach($no_update_files as $file)
	{
		if(strpos($file, '.php')!==false || strpos($file, '.css')!==false || strpos($file, '.js')!==false)
		{
			$new_path = $dv_directory . "/" . basename($file);
			copy('webCode/requiredFiles/' . $file, $new_path);
		}
	}

//copy over default main file but replace project name and project description
	$new_main_path = $dv_directory . "/main.php"; 
	$main_default_path = "webCode/main_default.php";

	$all_main_lines = array();
	$file = fopen($main_default_path, "r");
	while(! feof($file))
	{
		$currLine = fgets($file);
		strpos($currLine, 'PROJECTNAMEHERE')!==false ? $currLine = str_replace('PROJECTNAMEHERE', $project_name, $currLine): null;
		strpos($currLine, 'PROJECTDESCRIPTIONHERE')!==false ?  $currLine = str_replace('PROJECTDESCRIPTIONHERE', $project_description, $currLine): null;
		array_push($all_main_lines, $currLine);
	}
	fclose($file);
	$file = fopen($new_main_path, "w");
	foreach ($all_main_lines as $line) {
		fwrite($file, $line);
	}
	fclose($file);

	//copy($main_default_path, $new_main_path);


//copy over updateProjID files and reset $projectID=-1 to $projectID='charcode'
	$update_files = scandir('webCode/requiredFiles/updateProjID');

	foreach ($update_files as $filename) {
		if(strpos($filename, '.php')!==false || strpos($filename, '.js')!==false) {
			$new_path = $dv_directory . "/" .  $filename;
			$all_lines = array();
			$file = fopen('webCode/requiredFiles/updateProjID/'. $filename, "r");
			while(! feof($file))
			{
				$currLine = fgets($file);
				if (strpos($filename, 'renameData.php')===false)
				{
					strpos($currLine, '$projectID=-1')!==false ? $currLine = "\$projectID='" . $projectID . "';" . PHP_EOL : null;
					strpos($currLine, '$scope.projectID=-1;')!==false ? $currLine = "\$scope.projectID='" . $projectID . "';" . PHP_EOL : null;
					strpos($currLine, 'PROJECTNAMEHERE')!==false ? $currLine = str_replace('PROJECTNAMEHERE', $project_name, $currLine): null;
					strpos($currLine, 'PROJECTDESCRIPTIONHERE')!==false ?  $currLine = str_replace('PROJECTDESCRIPTIONHERE', $project_description, $currLine): null;
					array_push($all_lines, $currLine);
				}
				else
				{
					strpos($currLine, '$projectID=-1')!==false && strpos($currLine, 'currLine')===false ? $currLine = "\$projectID='" . $projectID . "';" . PHP_EOL : null;
					strpos($currLine, '$scope.projectID=-1;')!==false  && strpos($currLine, 'currLine')===false ? $currLine = "\$scope.projectID='" . $projectID . "';" . PHP_EOL : null;
					strpos($currLine, 'PROJECTNAMEHERE')!==false  && strpos($currLine, 'currLine')===false ? $currLine = str_replace('PROJECTNAMEHERE', $project_name, $currLine): null;
					strpos($currLine, 'PROJECTDESCRIPTIONHERE')!==false  && strpos($currLine, 'currLine')===false ?  $currLine = str_replace('PROJECTDESCRIPTIONHERE', $project_description, $currLine): null;
					array_push($all_lines, $currLine);
				}
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
catch (Exception $e) {
	$data = array("result"=>false, "message"=>"Unable to create a new project entry at this time. Please try again in a few moments.");
	echo(json_encode($data));
	return;
}

try
{
//add new project
	$query = "INSERT INTO projects (project_id, project_name, project_description, creator_user_id, creation_date, is_perm_project, last_activity) VALUES (:project_id, :project_name, :project_description, :creator_user_id, :creation_date, :is_perm_project, :last_activity)";
	$query_params = array(':project_id' => $projectID, ':project_name' => $project_name, ':project_description' => $project_description, ':creator_user_id' => $_SESSION['user'], ':creation_date' => $time, ':is_perm_project' => $is_perm_project, ':last_activity' => $time);
	$stmt = $db->prepare($query); 
	$result = $stmt->execute($query_params);

//add permission level (3)
	$query = "INSERT INTO project_permissions (project_id, user_id, permission_level) VALUES (:project_id, :user_id, :permission_level)";
	$query_params = array(':project_id' => $projectID, ':user_id' => $_SESSION['user'], ':permission_level' => 3);
	$stmt = $db->prepare($query); 
	$result = $stmt->execute($query_params);

//add main branch
	$main_branch_id = $projectID . "-1B";
	$query = "INSERT INTO project_branches (branch_id, project_id, branch_number, branch_name) VALUES (:branch_id, :project_id, :branch_number, :branch_name)";
	$query_params = array(':branch_id' => $main_branch_id, ':project_id' => $projectID, ':branch_number' => 1, ':branch_name' => 'Main');
	$stmt = $db->prepare($query); 
	$result = $stmt->execute($query_params);

//add empty project data summary
	$query = "INSERT INTO project_data_summary (project_id, branch_id) VALUES (:project_id, :branch_id)";
	$query_params = array(':branch_id' => $main_branch_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query); 
	$result = $stmt->execute($query_params);

//add project creation event
	$query = "INSERT INTO project_activity (project_id, activity, time, description) VALUES (:project_id, :activity, :time, :description)";
	$query_params = array(':project_id' => $projectID, ':activity' => 'CREATION', ':time' => $time, ':description' => "A new project named '" . $project_name . "' was created by " .  $_SESSION['pref'] . " " . $_SESSION['last'] . ".");
	$stmt = $db->prepare($query); 
	$result = $stmt->execute($query_params);


//add project visualizations and set to 0
	$query = "INSERT INTO project_data_visualizations (project_id, visualization_id, visualization_on) VALUES (:project_id, 'outlier', 0), (:project_id, 'volcano', 0), (:project_id, 'bar', 0), (:project_id, 'scatter', 0), (:project_id, 'pcacond', 0), (:project_id, 'pcarep', 0), (:project_id, 'go', 0), (:project_id, 'hcheatmap', 0)";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query); 
	$result = $stmt->execute($query_params);

//add max_project_node entries
	$query = "INSERT INTO project_max_nodes (project_id, max_branch_number, max_set_number, max_condition_number, max_replicate_number) VALUES (:project_id, :max_branch, :max_set, :max_condition, :max_replicate)";
	$query_params = array(':project_id' => $projectID, ':max_branch' => 1, ':max_set' => 0, ':max_condition' => 0, ':max_replicate' => 0);
	$stmt = $db->prepare($query); 
	$result = $stmt->execute($query_params);

}
catch (Exception $e) {
	$data = array("result"=>false, "message"=>"Unable to create a new project entry at this time. Please try again in a few moments.");
	echo(json_encode($data));
	return;
}

sleep(1);

$data = array("result"=>true, "message"=>"A new project called '" . $project_name . "' has been created. You can upload data, modify web portal visualizations, and invite collaborators to explore the project by selecting it from the dropdown menu and navigating to the 'Edit Project' page.");
echo(json_encode($data));
return;


//return success message indicating that users can upload data, modify web portal visualizations, and invite collaborators to explore the project under the edit project tab.



function randomKey($length) {
	$pool = array_merge(range(0,9), range('a', 'd'),range('f', 'z'),range('A', 'D'),range('F', 'Z'));
	$key = "";
	for($i=0; $i < $length; $i++) {
		$key .= $pool[mt_rand(0, count($pool) - 1)];
	}
	return $key;
}