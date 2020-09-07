<?php
require("config.php");
if (empty($_SESSION['user'])) {
    header("Location: index.html");
    die("Redirecting to index.html");
}

$projectID = $_POST['pi'];
$alert = false;
//$projectID = "In9lgXX";

//check to make sure user has delete permissions
$data = array();
 $query = " 
            SELECT 
                1 
            FROM project_permissions 
            WHERE 
                project_id=:project_id AND user_id=:user_id AND permission_level=3
        "; 
        $query_params = array( 
            ':project_id' => $projectID,
            ':user_id' => $_SESSION['user'] 
        ); 

$stmt = $db->prepare($query); 
$result = $stmt->execute($query_params); 
$row = $stmt->fetch(); 
if(!$row){

	$data = array("result"=>false, "message"=>"You do not have permission to delete this project.");
    echo(json_encode($data));
    return;
}

if ($alert==true)
{
	//if alert then send out an email -- leave blank for now
}

//copy existing project records to into deleted_projects
$project_name = "";
$creator_user_id="";
$project_description="";
$creation_date="";
$last_activity="";

$query = "SELECT * FROM projects WHERE project_id=:project_id";
 $query_params = array( 
            ':project_id' => $projectID
        ); 
 $stmt = $db->prepare($query); 
$result = $stmt->execute($query_params); 
$row = $stmt->fetch(); 
$project_name = $row['project_name'];
$creator_user_id = $row['creator_user_id'];
$project_description=$row['project_description'];
$creation_date=$row['creation_date'];
$last_activity=$row['last_activity'];

$query = "INSERT INTO deleted_projects (project_id, creator_user_id, project_name, project_description, creation_date, last_activity, date_deleted, files_deleted, deletion_complete) VALUES ("
	 . ":project_id, :creator_user_id, :project_name, :project_description, :creation_date, :last_activity, :date_deleted, :files_deleted, :deletion_complete)";
 $query_params = array( 
            ':project_id' => $projectID,
            ':creator_user_id' => $creator_user_id,
            ':project_name' => $project_name,
            ':project_description' => $project_description,
            ':creation_date' => $creation_date,
            ':last_activity' => $last_activity,
            ':date_deleted' => date("Y-m-d H:i:s"),
            ':files_deleted' => 0,
            ':deletion_complete' => 0
        ); 

$stmt = $db->prepare($query); 
$result = $stmt->execute($query_params); 

$query = "INSERT INTO data_deletion_queue (project_id, identifier, identifier_type, deletion_time) VALUES (:project_id, :identifier, :identifier_type, :deletion_time)";
$query_params = array(':project_id' => $projectID, ':identifier' => $projectID, ':identifier_type' => 'project_id', ':deletion_time' => date("Y-m-d H:i:s"));
$stmt = $db->prepare($query); 
$result = $stmt->execute($query_params); 

//make new entry in deleted_project_records

$query = "INSERT INTO deleted_project_records (project_id) VALUES ("
	. ":project_id)";
 $query_params = array( 
            ':project_id' => $projectID,
        ); 
$stmt = $db->prepare($query); 
$result = $stmt->execute($query_params); 


//delete project directories
rrmdir('DV/' . $projectID);
rrmdir('server/php/files/' . $projectID);

//delete project entry

$query = "DELETE FROM projects WHERE project_id=:project_id";
 $query_params = array( 
            ':project_id' => $projectID,
        ); 
$stmt = $db->prepare($query); 
$result = $stmt->execute($query_params); 
sleep(1.4);

$data = array("result"=>true, "message"=>"Your project has been deleted from the server.");
    echo(json_encode($data));
    return;


function rrmdir($dir)
{
    array_map('unlink', glob("$dir/*.*"));
    rmdir($dir);
}

/*function rrmdir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (filetype($dir."/".$object) == "dir") 
           rrmdir($dir."/".$object); 
        else unlink   ($dir."/".$object);
      }
    }
    reset($objects);
    rmdir($dir);
  }
 }*/