<?php

require("config.php");

$query = "SELECT * FROM data_deletion_queue WHERE completed=0 LIMIT 1";
$stmt = $db->prepare($query);
$result = $stmt->execute();
$row = $stmt->fetch();
if(!$row)
{
	return;
}

$identifier = $row['identifier'];
$identifier_type=$row['identifier_type'];
$current_table = $row['current_table'];
$key = $row['key'];

//echo($current_table);

//Table order (set): data_condition_data, data_descriptive_statistics, data_outlier_analysis, data_pca_condition, data_pca_replicate, data_query_terms, data_replicate_data
//Table order (branch): same as above, project_data_summary
//Table order (project): data_condition_data, data_descriptive_statistics, data_feature_metadata, data_outlier_analysis, data_pca_condition, data_pca_replicate, data_query_terms,
//data_replicate_data, data_unique_identifiers, project_activity, project_branches, project_conditions, project_data_summary, project_data_visualizations, project_files,
//project_file_headers, project_invitations, project_permissions, project_replicates, project_sets.

if($identifier_type==="set_id")
{
	switch($current_table)
	{
		case "data_condition_data":
		$query = "DELETE FROM data_condition_data WHERE set_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_descriptive_statistics' WHERE data_deletion_queue.key=:key";
			$query_params = array(':key' => (int)$key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_descriptive_statistics":
		$query = "DELETE FROM data_descriptive_statistics WHERE set_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_outlier_analysis' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_outlier_analysis":
		$query = "DELETE FROM data_outlier_analysis WHERE set_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_pca_condition' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_pca_condition":
		$query = "DELETE FROM data_pca_condition WHERE set_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_pca_replicate' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_pca_replicate":
		$query = "DELETE FROM data_pca_replicate WHERE set_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_query_terms' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_query_terms":
		$query = "DELETE FROM data_query_terms WHERE set_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_replicate_data' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_replicate_data":
		$query = "DELETE FROM data_replicate_data WHERE set_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_data_summary' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "project_data_summary":
		$query = "DELETE FROM project_data_summary WHERE set_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET completed=1 WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
	}
}

if($identifier_type==="branch_id")
{
	switch($current_table)
	{
		case "data_condition_data":
		$query = "DELETE FROM data_condition_data WHERE branch_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_descriptive_statistics' WHERE data_deletion_queue.key=:key";
			$query_params = array(':key' => (int)$key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_descriptive_statistics":
		$query = "DELETE FROM data_descriptive_statistics WHERE branch_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_outlier_analysis' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_outlier_analysis":
		$query = "DELETE FROM data_outlier_analysis WHERE branch_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_pca_condition' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_pca_condition":
		$query = "DELETE FROM data_pca_condition WHERE branch_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_pca_replicate' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_pca_replicate":
		$query = "DELETE FROM data_pca_replicate WHERE branch_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_query_terms' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_query_terms":
		$query = "DELETE FROM data_query_terms WHERE branch_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_replicate_data' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_replicate_data":
		$query = "DELETE FROM data_replicate_data WHERE branch_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_data_summary' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "project_data_summary":
		$query = "DELETE FROM project_data_summary WHERE branch_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET completed=1 WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
	}
}

if($identifier_type==="project_id")
{
	switch($current_table) //Only worry about deleted project records here.
	{
		case "data_condition_data" :
		$query = "DELETE FROM data_condition_data WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_descriptive_statistics' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_descriptive_statistics" :
		$query = "DELETE FROM data_descriptive_statistics WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_feature_metadata' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "data_feature_metadata" :
		$query = "DELETE FROM data_feature_metadata WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_outlier_analysis' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "data_outlier_analysis" :
		$query = "DELETE FROM data_outlier_analysis WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_pca_condition' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "data_pca_condition" :
		$query = "DELETE FROM data_pca_condition WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_pca_replicate' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_pca_replicate" :
		$query = "DELETE FROM data_pca_replicate WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_query_terms' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "data_query_terms" :
		$query = "DELETE FROM data_query_terms WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_replicate_data' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_replicate_data" :
		$query = "DELETE FROM data_replicate_data WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='data_unique_identifiers' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "data_unique_identifiers" :
		$query = "DELETE FROM data_unique_identifiers WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_activity' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "project_activity" :
		$query = "DELETE FROM project_activity WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_branches' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "project_branches" :
		$query = "DELETE FROM project_branches WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_conditions' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "project_conditions" :
		$query = "DELETE FROM project_conditions WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_data_summary' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "project_data_summary" :
		$query = "DELETE FROM project_data_summary WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_data_visualizations' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "project_data_visualizations" :
		$query = "DELETE FROM project_data_visualizations WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_files' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "project_files" :
		$query = "DELETE FROM project_files WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_file_headers' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "project_file_headers" :
		$query = "DELETE FROM project_file_headers WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_invitations' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
		case "project_invitations" :
		$query = "DELETE FROM project_invitations WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_permissions' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "project_permissions" :
		$query = "DELETE FROM project_permissions WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_replicates' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "project_replicates" :
		$query = "DELETE FROM project_replicates WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET current_table='project_sets' WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break; 
		case "project_sets" :
		$query = "DELETE FROM project_sets WHERE project_id=:id LIMIT 5000";
		$query_params = array( ':id' => $identifier);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$count = $stmt->rowCount();
		if ($count===0)
		{
			$query = "UPDATE data_deletion_queue SET completed=1 WHERE data_deletion_queue.key=:key";
			$query_params = array( ':key' => $key);
			$stmt = $db->prepare($query);
			$result = $stmt->execute($query_params);
		}
		break;
	}
}