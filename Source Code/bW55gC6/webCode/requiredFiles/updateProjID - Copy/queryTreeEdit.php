<?php

require("config.php");
if(empty($_SESSION['user']))
{
    header("Location: index.html");
    die("Redirecting to index.html");
}

$projectID=-1;

$list = array();

$query = "SELECT * FROM projects WHERE project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach($row as $entry)
{
	array_push($list, array('name'=>$entry['project_id'], 'value'=>$entry['project_name'], 'parent' => 'null', 'x0' => 0, 'y0' => 0, 'control' => "0"));
}

$query = "SELECT project_branches.*, projects.project_id FROM project_branches JOIN projects ON projects.project_id=project_branches.project_id WHERE project_branches.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach($row as $entry)
{
	array_push($list, array('name'=>$entry['branch_id'], 'value'=>$entry['branch_name'], 'parent' => $entry['project_id'], 'x0' => 0, 'y0' => 0, 'control' => "0"));
}

$query = "SELECT project_sets.*, project_branches.project_id FROM project_sets JOIN project_branches ON project_branches.branch_id=project_sets.branch_id WHERE project_sets.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach($row as $entry)
{
	array_push($list, array('name'=>$entry['set_id'], 'value'=>$entry['set_name'], 'parent' => $entry['branch_id'], 'x0' => 0, 'y0' => 0, 'control' => "0"));
}

$query = "SELECT project_conditions.*, project_sets.set_id FROM project_conditions JOIN project_sets ON project_sets.set_id=project_conditions.set_id WHERE project_conditions.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach($row as $entry)
{
	array_push($list, array('name'=>$entry['condition_id'], 'value'=>$entry['condition_name'], 'parent' => $entry['set_id'], 'x0' => 0, 'y0' => 0, 'control' => $entry['is_control']));
}

$query = "SELECT project_replicates.*, project_conditions.condition_id FROM project_replicates JOIN project_conditions ON project_conditions.condition_id=project_replicates.condition_id WHERE project_replicates.project_id=:project_id";
$query_params = array(':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
foreach($row as $entry)
{
	array_push($list, array('name'=>$entry['replicate_id'], 'value'=>$entry['replicate_name'], 'parent' => $entry['condition_id'], 'x0' => 0, 'y0' => 0, 'control' => $entry['is_control']));
}

echo(json_encode($list));

?>
