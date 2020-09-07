<?php
require("config.php");

if(empty($_SESSION['user']))
{
	die();
}
header('Content-Type: text/plain; charset=utf-8');
$projectID=-1;


try {

    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.

	if (
		!isset($_FILES['file']['error']) ||
		is_array($_FILES['file']['error'])
		) {
		throw new RuntimeException('Invalid parameters.');
}

    // Check $_FILES['upfile']['error'] value.
switch ($_FILES['file']['error']) {
	case UPLOAD_ERR_OK:
	break;
	case UPLOAD_ERR_NO_FILE:
	throw new RuntimeException('No file sent.');
	case UPLOAD_ERR_INI_SIZE:
	case UPLOAD_ERR_FORM_SIZE:
	throw new RuntimeException('Exceeded filesize limit.');
	default:
	throw new RuntimeException('Unknown errors.');
}


    // You should also check filesize here. 
if ($_FILES['file']['size'] > 50000000) {
	throw new RuntimeException('Exceeded filesize limit.');
}

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
$finfo = new finfo(FILEINFO_MIME_TYPE);
if (false === $ext = array_search(
	$finfo->file($_FILES['file']['tmp_name']),
	array(
		'txt' => 'text/plain',
		),
	true
	)) {
	throw new RuntimeException('Invalid file format.');
}


$query = "SELECT * FROM project_temporary_files WHERE uploader_user_id=:user_id AND project_id=:project_id";
$query_params = array(':user_id' =>  $_SESSION['user'], ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();


$data = array();
foreach ($row as $entry) {
	array_push($data, $entry);
}

foreach ($data as $row) {
	if (file_exists('../../server/php/files/' . $projectID . "/" . $row['file_name']))
	{
	unlink('../../server/php/files/' . $projectID . "/" . $row['file_name']);
}
}

$query = "DELETE FROM project_temporary_files WHERE uploader_user_id=:user_id AND project_id=:project_id";
$query_params = array(':user_id' =>  $_SESSION['user'], ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);

$argLine = "INSERT INTO project_temporary_files (project_id, uploader_user_id, original_file_name, file_name, upload_time) VALUES (:project_id, :user_id, :original_file_name, :file_name, :upload_time)";
$query_params = array(':project_id' => $projectID, ':user_id' => $_SESSION['user'], ':original_file_name' => $_FILES['file']['name'], ':file_name' => sha1_file($_FILES['file']['tmp_name']) . '.txt', ':upload_time' => date("Y-m-d H:i:s"));
try { 
	$stmt = $db->prepare($argLine); 
	$result = $stmt->execute($query_params); 
	 //$row = $stmt->fetch(); 
} 
catch(PDOException $ex){ die("Failed to run query: " . $ex->getMessage());} 

$file = fopen($_FILES['file']['tmp_name'],"r");
$firstLine = fgets($file);

fclose($file);

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
   //  On this example, obtain safe unique name from its binary data.
    if (!move_uploaded_file(
        $_FILES['file']['tmp_name'],
       sprintf('../../server/php/files/' . $projectID . '/%s%s',
           sha1_file($_FILES['file']['tmp_name']),
           '.txt'
       )
 )) 
    $firstLine =  rtrim($firstLine);
echo (rtrim($firstLine));


} catch (RuntimeException $e) {

	echo $e->getMessage();

}

?>
