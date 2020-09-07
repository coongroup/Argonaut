<?php
require("config.php");
if(empty($_SESSION['user']))
{
	header("Location: index.html");
	die("Redirecting to index.html");
}
if ($_SESSION['user']!==1 && $_SESSION['user']!=="1")
{
    die();
}

$query = "SELECT * FROM projects WHERE project_name !='Mitochondrial Protease Profiling'";
$stmt = $db->prepare($query);
$result = $stmt->execute();
$row = $stmt->fetchAll();

echo(json_encode($row));

foreach($row as $entry)
{
	$query = "DELETE FROM projects WHERE project_id=:project_id; 
	DELETE FROM process_queue WHERE project_id=:project_id;
	DELETE FROM data_query_terms WHERE project_id=:project_id;
	DELETE FROM data_condition_data WHERE project_id=:project_id;
	DELETE FROM data_replicate_data WHERE project_id=:project_id;
	DELETE FROM data_descriptive_statistics WHERE project_id=:project_id;
	DELETE FROM data_feature_metadata WHERE project_id=:project_id;
	DELETE FROM data_outlier_analysis WHERE project_id=:project_id;
	DELETE FROM data_pca_condition WHERE project_id=:project_id;
	DELETE FROM data_pca_replicate WHERE project_id=:project_id;
	DELETE FROM data_unique_identifiers WHERE project_id=:project_id;
	 DELETE FROM project_file_headers WHERE project_id=:project_id; 
	 DELETE FROM project_files WHERE project_id=:project_id; 
	 DELETE FROM project_permissions WHERE project_id=:project_id; 
	 DELETE FROM project_conditions WHERE project_id=:project_id; 
	 DELETE FROM project_replicates WHERE project_id=:project_id; 
	 DELETE FROM project_branches WHERE project_id=:project_id; 
	 DELETE FROM project_activity WHERE project_id=:project_id; 
	 DELETE FROM project_data_summary WHERE project_id=:project_id; 
	 DELETE FROM project_max_nodes WHERE project_id=:project_id; 
	 DELETE FROM project_sets WHERE project_id=:project_id; 
	 DELETE FROM project_data_visualizations WHERE project_id=:project_id;";
	$query_params = array(':project_id' => $entry['project_id']);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);


	$dv_dir = "DV/" . $entry['project_id'];
	$server_dir = "server/php/files/" . $entry['project_id'];


	array_map('unlink', glob("$dv_dir/*.*"));
	array_map('unlink', glob("$server_dir/*.*"));
	rmdir($dv_dir);
	rmdir($server_dir);
}