<?php

require("config.php");

if(empty($_SESSION['user']))
{
    header("Location: ../../index.html");
    die("Redirecting to ../../index.html");
}


$projectID=-1;
$query = "SELECT a.sending_user_id, b.first_name AS s_first, b.last_name AS s_last, a.invitation_send_time, a.invitation_accept_time, a.invitation_accepted, a.permission_level, c.first_name AS r_first, c.last_name AS r_last, a.email FROM project_invitations AS a LEFT JOIN users AS b ON a.sending_user_id=b.id LEFT JOIN users AS c ON a.accepting_user_id=c.id WHERE a.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

$data = array();
foreach($row as $entry)
{
	$send_date = date_create($entry['invitation_send_time']);
	$accept_date = date_create($entry['invitation_accept_time']);
	$invitation = $entry['invitation_accepted']==="0" ? "" . date_format($send_date, 'l F jS, Y \a\t g:ia') : "" . date_format($accept_date, 'l F jS, Y \a\t g:ia') ;
	$permission_level = "";
	switch($entry['permission_level'])
	{
		case "1" : $permission_level .= "Read Only"; break;
		case "2" : $permission_level .= "Read & Edit"; break;
		case "3" : $permission_level .= "Project Owner"; break;
	}
	$invited_by = "" . $entry['s_first'] . " " . $entry['s_last'];
	$accepted = $entry['invitation_accepted']==="0" ? 0 : 1;
	$display_name = $entry['invitation_accepted']==="0" ? $entry['email'] : $entry['r_first'] . " " . $entry['r_last'];
	$color = $entry['invitation_accepted']==="0" ? "bg-red" : "bg-green";
	$label = $entry['invitation_accepted']==="0" ? "label-danger" : "label-success";
	$invite_status = $entry['invitation_accepted']==="0" ? "Invitation Sent: " : "Invitation Accepted: ";
	array_push($data, array('invitation'=> $invitation, 'permission' => $permission_level, 'invited' => $invited_by, 'accepted' => $accepted, 'display' => $display_name, 'color' => $color, 'label'=>$label, 'inviteStatus' => $invite_status));
}

echo(json_encode($data));

