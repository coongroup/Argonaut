<?php
require("config.php");
if(empty($_SESSION['user']))
{
    header("Location: index.html");
    die("Redirecting to index.html");
}

$projectCode = $_POST['pc'];
$query = "SELECT * FROM project_invitations WHERE unique_code=:unique_code AND invitation_accepted=0";
$query_params = array(':unique_code' => $projectCode);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

$outVal = "false";
if($row)
{
	$outVal = "true";
}
echo($outVal); 