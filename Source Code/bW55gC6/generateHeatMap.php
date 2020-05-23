<?php

require('config.php');
$projectID='bW55gC6';

while (true)
{
	$query = "SELECT * FROM hierarchical_clustering_inputs WHERE project_id=:project_id AND completed=0 AND running=0";
	$query_params = array(':project_id'=>$projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		break;
	}

	$branchID = $row['branch_id'];
	$analysisID = $row['analysis_id'];
	$useConds = $row['use_conditions'];
	$excludedNodes = json_decode($row['excluded_nodes'], true);
	$rowLink = $row['row_linkage'];
	$rowDistance = $row['row_distance'];
	$colLink = $row['column_linkage'];
	$colDistance = $row['column_distance'];
	$stringRep = $row['string_representation'];

	try{

		$query = "UPDATE hierarchical_clustering_inputs SET running=1 WHERE analysis_id=:analysis_id";
		$query_params = array(':analysis_id' => $analysisID);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);


		$row_cluster_distance_dictionary = array();
		$row_cluster_dictionary = array();
		$row_dictionary = array();

		$column_cluster_distance_dictionary = array();
		$column_cluster_dictionary = array();
		$column_dictionary = array();

		$index_to_molecule_id_dictionary = array();
		$index_to_condition_id_dictionary = array();

		$current_cluster_id = 1;

		$cycles = 0;
		$expectedCycles = 0;
		$percentComplete = 0;
		$molecule_index = 1;
		$condition_index = 1;


		set_error_handler("HandleErrorHeatMap");
		GenerateHeatMap($branchID, $analysisID, $useConds, $excludedNodes, $rowLink, $rowDistance, $colLink, $colDistance, $db, $projectID);
	}
	catch(Exception $e)
	{
		FinishProcessingFailure($analysisID, $db);
	}
}

function HandleErrorHeatMap($errno, $errstr, $errfile, $errline)
{
	throw new Exception('Inside HandleError');
}


function GenerateHeatMap($branchID, $analysisID, $useConds, $excludedNodes, $rowLink, $rowDistance, $colLink, $colDistance, $db, $project_id)
{
	global $row_cluster_dictionary;
	global $row_cluster_distance_dictionary;
	global $row_dictionary;
	global $column_cluster_dictionary;
	global $column_cluster_distance_dictionary;
	global $column_dictionary;
	global $index_to_molecule_id_dictionary;
	global $index_to_condition_id_dictionary;
	global $expectedCycles;
	global $molecule_index;
	global $condition_index;
	global $projectID;
	global $branchID;

	//Get original quant data in an array
	$original_2d_array = null;
	if($useConds)
	{
		$original_2d_array = Get2DArrayConditions($analysisID, $branchID, $excludedNodes, $db, $projectID);
	}
	else
	{
		$original_2d_array = Get2DArrayReplicates($analysisID, $branchID, $excludedNodes, $db, $projectID);
	}

	UpdateIncludedNodes($original_2d_array, $analysisID, $db);

	//Get your row dictionary (molecule_identifier, array of quant values--this only contains molecules observed across all conditions)
	$row_dictionary = GetRowDictionary($original_2d_array);
	$row_cluster_dictionary = CreateInitialClusters($row_dictionary);
	
		//Update original_2d_array to include only molecules observed across all conditions
	$tmp_original_2d_array = array();
	foreach ($original_2d_array as $condition => $moldict) {
		$tmp_original_2d_array[$condition] = array();
		foreach ($moldict as $key => $value) {
			if(array_key_exists($key, $row_dictionary))
			{
				$tmp_original_2d_array[$condition][$key] = $value;
			}
		}
	}
	$original_2d_array = $tmp_original_2d_array;
	
	//Write file after completing this process
	$file = fopen("heatMap_" . $analysisID . ".txt", "w");
	fwrite($file, json_encode($original_2d_array));
	fclose($file);


	$expectedCycles = (2 * (count($original_2d_array)-2)) + (2 * (count($row_cluster_dictionary)-2));

	//echo(json_encode($row_cluster_dictionary));
	DoClustering( $rowLink, $rowDistance, true, $analysisID, $db);

	$column_dictionary = GetColumnDictionary($original_2d_array, $row_dictionary);
	$original_2d_array=null; 
	$row_dictionary = null;
	gc_collect_cycles();

	//Get initial column clusters and add them to a dictionary
	$column_cluster_dictionary = CreateInitialClusters($column_dictionary);

	//Get original column cluster keys
	//$current_clusters = array_keys($column_cluster_dictionary);

	//Create a data structure for holding column cluster distances (again, analogous to dictionary of dictionaries in C#)
	$column_cluster_distance_dictionary = array();

	DoClustering( $colLink, $colDistance, false, $analysisID, $db);

	$molecule_index = 1;
	$condition_index = 1;

	$row_cluster_start = current(array_reverse(array_keys($row_cluster_dictionary)));
	$column_cluster_start = current(array_reverse(array_keys($column_cluster_dictionary)));

	OrganizeRows($row_cluster_start);

	$row_cluster_distance_dictionary = null;
	gc_collect_cycles();

	OrganizeColumns($column_cluster_start);

	usort($row_cluster_dictionary, "cmp");
	usort($column_cluster_dictionary, "cmp");

/*	$file = fopen("RowClusters.txt", "w");
	fwrite($file, json_encode($row_cluster_dictionary));
	fclose($file);

	$file = fopen("ColumnClusters.txt", "w");
	fwrite($file, json_encode($column_cluster_dictionary));
	fclose($file);


	//echo(json_encode($row_cluster_dictionary));
	echo("here");*/

	$query = "INSERT INTO hierarchical_clustering_results (analysis_id, column_clusters, row_clusters, project_id, branch_id) VALUES (:analysis_id, :column_clusters, :row_clusters, :project_id, :branch_id)";
	$query_params = array(':analysis_id' => $analysisID, ':column_clusters' => json_encode($column_cluster_dictionary), ':row_clusters' => json_encode($row_cluster_dictionary), ':project_id' => $projectID, ':branch_id'=>$branchID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	FinishProcessing($analysisID, $db);

}

function cmp($a, $b)
{
	if ($a->d > $b->d)
	{
		return 1;
	}
	if ($a->d<$b->d)
	{
		return -1;
	}
	return 0;
}

function UpdateIncludedNodes($quantArray, $analysisID, $db)
{
	$includedArray = array();
	foreach ($quantArray as $key => $value) {
		array_push($includedArray, $key);
	}
	$query = "UPDATE hierarchical_clustering_inputs SET included_nodes=:included_nodes WHERE analysis_id=:analysis_id";
	$query_params = array(':included_nodes' => json_encode($includedArray), ':analysis_id' => $analysisID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

function UpdateProcessing($analysisID, $db)
{
	$query = "UPDATE hierarchical_clustering_inputs SET running=1 WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id' => $analysisID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

function FinishProcessing($analysisID, $db)
{
	$query = "UPDATE hierarchical_clustering_inputs SET running=0, completed=1, progress=100 WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id' => $analysisID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

function FinishProcessingFailure($analysisID, $db)
{
	$query = "UPDATE hierarchical_clustering_inputs SET running=0, completed=1, progress=100, failed=1 WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id' => $analysisID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

function Get2DArrayConditions($analysis_id, $branch_id, $excludedNodes, $db, $project_id)
{
	//figure out if you should use control normalization

	$query = "SELECT quant FROM project_files WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branch_id);
	$use_control = true;
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	foreach ($row as $entry) {
		$quantObjs = json_decode($entry['quant'], true);
		$controlsObjs = array_filter($quantObjs, "ControlPresent");
		if (count($controlsObjs)===0)
		{
			$use_control = false;
		}
	}

	if($use_control)
	{
		$query = "UPDATE hierarchical_clustering_inputs SET control_normalized=1 WHERE analysis_id=:analysis_id";
		$query_params = array(':analysis_id'=>$analysis_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);

		$tmpArray = array();
		foreach ($excludedNodes as $entry) {
			$tmpArray[$entry]="";
		}
		$query = "SELECT a.fold_change_control_norm AS fc, a.unique_identifier_id AS mol_id, a.condition_id AS cond_id FROM data_descriptive_statistics AS a JOIN project_conditions AS b ON a.condition_id=b.condition_id WHERE a.branch_id=:branch_id AND b.is_control=0";
		$query_params = array(':branch_id' => $branch_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		$return_array = array();
		foreach ($row as $entry) {
			if (!array_key_exists($entry['cond_id'], $tmpArray))
			{
				if(!array_key_exists($entry['cond_id'], $return_array))
				{
					$return_array[$entry['cond_id']] = array();
				}
				$return_array[$entry['cond_id']][$entry['mol_id']] = round($entry['fc'],5);
			}
		}
		return $return_array;
	}
	else
	{
		$query = "UPDATE hierarchical_clustering_inputs SET control_normalized=0 WHERE analysis_id=:analysis_id";
		$query_params = array(':analysis_id'=>$analysis_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);

		$tmpArray = array();
		foreach ($excludedNodes as $entry) {
			$tmpArray[$entry]="";
		}
		$query = "SELECT a.fold_mean_control_norm AS fc, a.unique_identifier_id AS mol_id, a.condition_id AS cond_id FROM data_descriptive_statistics AS a JOIN project_conditions AS b ON a.condition_id=b.condition_id WHERE a.branch_id=:branch_id AND b.is_control=0";
		$query_params = array(':branch_id' => $branch_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		$return_array = array();
		foreach ($row as $entry) {
			if (!array_key_exists($entry['cond_id'], $tmpArray))
			{
				if(!array_key_exists($entry['cond_id'], $return_array))
				{
					$return_array[$entry['cond_id']] = array();
				}
				$return_array[$entry['cond_id']][$entry['mol_id']] = round($entry['fc'],5);
			}
		}
		return $return_array;
	}
}

function ControlPresent($var)
{
	return $var['control']==="Yes";
}

function Get2DArrayReplicates($analysis_id, $branchID, $excludedNodes, $db, $project_id)
{
	//figure out which of the 2d replicate methods you need to call 
	//i.e., figure out if all sets have a specified control.
	$query = "SELECT quant FROM project_files WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branchID);
	$use_control = true;
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	foreach ($row as $entry) {
		$quantObjs = json_decode($entry['quant'], true);
		$controlsObjs = array_filter($quantObjs, "ControlPresent");
		if (count($controlsObjs)===0)
		{
			$use_control = false;
		}
	}
	if($use_control)
	{
		$query = "UPDATE hierarchical_clustering_inputs SET control_normalized=1 WHERE analysis_id=:analysis_id";
		$query_params = array(':analysis_id'=>$analysis_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		return Get2DArrayReplicatesControl($branchID, $excludedNodes, $db, $project_id);
	}
	else
	{
		$query = "UPDATE hierarchical_clustering_inputs SET control_normalized=0 WHERE analysis_id=:analysis_id";
		$query_params = array(':analysis_id'=>$analysis_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		return Get2DArrayReplicatesNoControl($branchID, $excludedNodes, $db, $project_id);
	}

}

function Get2DArrayReplicatesControl($branchID, $excludedNodes, $db, $project_id)
{
	//get all unique control condition ids and store in a dict (rep_id -> control condition id)
	//query all avg quant data for these conditions

	//create tmp associative array for excludedNodes
	$tmpExcludedNodeArray = array();
	foreach ($excludedNodes as $entry) {
		$tmpExcludedNodeArray[$entry]="";
	}

	$rep_quant_dict = array();
	$rep_to_set_dict = array(); //[replicate_id]=set_id
	$set_to_control_condition_dict = array(); //[set_id][condition_id]
	$set_control_quant_dict = array(); //[set_id][mol_id] **contains control quant data**
	//query all reps from the branch

	$query = "SELECT replicate_id, set_id, condition_id, is_control FROM project_replicates WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branchID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

//if control --> update [set_id][condition_id]
	//else if not an excluded node $rep_quant_dict[replicate_id] = array();
	foreach ($row as $entry) {
		if($entry['is_control']==="1" || $entry['is_control']===1)
		{
			$set_to_control_condition_dict[$entry['set_id']]=$entry['condition_id'];
		}
		else
		{
			if(!array_key_exists($entry['replicate_id'], $tmpExcludedNodeArray))
			{
				$rep_quant_dict[$entry['replicate_id']] = array();
				$rep_to_set_dict[$entry['replicate_id']] = $entry['set_id'];
			}
		}
	}
	
	//foreach set_id/condition_id pair query all quant data from data_condition_data and populate
	foreach ($set_to_control_condition_dict as $key => $value) {
		$set_control_quant_dict[$key] = array();
		$query = "SELECT avg_quant_value, unique_identifier_id FROM data_condition_data WHERE condition_id=:condition_id";
		$query_params = array(':condition_id' => $value);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		foreach ($row as $entry) {
			$set_control_quant_dict[$key][$entry['unique_identifier_id']] = round($entry['avg_quant_value'], 6);
		}
	}

	//query all replicate quant data from data_replicate_data
	//foreach quant val check if replicate array key exists in rep_quant_dict and set_control_quant_dict and then populate rep_quant_dict with control normalized value
	$query = "SELECT unique_identifier_id, quant_value_log2, replicate_id, set_id FROM data_replicate_data WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branchID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	foreach ($row as $entry) {
		if(array_key_exists($entry['replicate_id'], $rep_quant_dict))
		{
			if(array_key_exists($entry['unique_identifier_id'], $set_control_quant_dict[$rep_to_set_dict[$entry['replicate_id']]]))
			{
				$rep_quant_dict[$entry['replicate_id']][$entry['unique_identifier_id']] = round(($entry['quant_value_log2'] -  $set_control_quant_dict[$rep_to_set_dict[$entry['replicate_id']]][$entry['unique_identifier_id']]), 6);
			}
		}
	}

	return $rep_quant_dict;

}

function Get2DArrayReplicatesNoControl($branchID, $excludedNodes, $db, $project_id)
{
	//create tmp associative array for excludedNodes
	$tmpExcludedNodeArray = array();
	foreach ($excludedNodes as $entry) {
		$tmpExcludedNodeArray[$entry]="";
	}

	$rep_quant_dict = array(); //[replicate_id][mol_id]
	$rep_to_set_dict = array(); //[replicate_id][set_id]
	$set_mean_sum_dict = array(); //[set_id][mol_id]
	$set_mean_count_dict = array(); //[set_id][mol_id]

	//$query all reps from the branch
	$query = "SELECT replicate_id, set_id, condition_id FROM project_replicates WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branchID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	foreach ($row as $entry) {
		if(!array_key_exists($entry['replicate_id'], $tmpExcludedNodeArray))
		{
			$rep_quant_dict[$entry['replicate_id']] = array();
			$rep_to_set_dict[$entry['replicate_id']] = $entry['set_id'];
			$set_mean_sum_dict[$entry['set_id']] = array();
			$set_mean_count_dict[$entry['set_id']] = array();
		}
	}

	//if not an excluded node add to $rep_quant_dict, rep_to_set_dict, set_mean_sum_dict, and set_mean_count_dict
	//$rep_quant_dict[$replicate_id]=array();
	//$rep_to_set_dict[$replicate_id]=$set_id; ...

	//query all replicate quant data from data_replicate_data

	//foreach result if replicate_id array_key_exists in rep_quant_dict add the molecule, add quant val to set_mean_sum_dict and increment set_mean_count_dict
	$query = "SELECT unique_identifier_id, quant_value_log2, replicate_id, set_id FROM data_replicate_data WHERE branch_id=:branch_id";
	$query_params = array(':branch_id' => $branchID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	foreach ($row as $entry) {
		if(array_key_exists($entry['replicate_id'], $rep_quant_dict))
		{
			$quantVal = round($entry['quant_value_log2'], 6);
			$mol_id = $entry['unique_identifier_id'];
			$rep_id = $entry['replicate_id'];
			$set_id = $entry['set_id'];
			if(!array_key_exists($mol_id, $set_mean_sum_dict[$set_id]))
			{
				$set_mean_sum_dict[$set_id][$mol_id] = 0;
				$set_mean_count_dict[$set_id][$mol_id]=0;
			}
			$rep_quant_dict[$rep_id][$mol_id] = $quantVal;
			$set_mean_sum_dict[$set_id][$mol_id]+=$quantVal;
			$set_mean_count_dict[$set_id][$mol_id]++;
		}
	}

	foreach ($set_mean_count_dict as $set_id => $mol_array) {
		foreach ($mol_array as $mol_id => $meas_count) {
			$set_mean_sum_dict[$set_id][$mol_id]/=$meas_count;
		}
	}

	foreach ($rep_quant_dict as $rep_id => $quant_array) {
		$set_id = $rep_to_set_dict[$rep_id];
		foreach ($quant_array as $mol_id => $quant_val) {
			$avg_val = $set_mean_sum_dict[$set_id][$mol_id];
			$rep_quant_dict[$rep_id][$mol_id]-=$avg_val;
			$rep_quant_dict[$rep_id][$mol_id] = round($rep_quant_dict[$rep_id][$mol_id],6);
		}
	}

	//foreach entry in set_mean_sum_dict entry/=set_mean_count_dict entry
	//subtract amount from each entry in rep_quant_dict
	return $rep_quant_dict;
}


function OrganizeRows($currentID)
{
	global $row_cluster_dictionary;
	global $index_to_molecule_id_dictionary;
	global $molecule_index;
	global $row_cluster_distance_dictionary;
	global $projectID;

	$cluster_obj = $row_cluster_dictionary[$currentID];

	if ($cluster_obj->MID!=="")
	{
		//$index_to_molecule_id_dictionary[$molecule_index] = $cluster_obj->MID;
		//$cluster_obj->MID = $project_id + $cluster_obj->MID;
		$cluster_obj->x=1;
		$cluster_obj->y=$molecule_index;
		$molecule_index++;
		return;
	}
	$min_clust = min($cluster_obj->C[0], $cluster_obj->C[1]);
	$max_clust = max($cluster_obj->C[0], $cluster_obj->C[1]);
	$cluster_obj->d = $row_cluster_distance_dictionary[$min_clust][$max_clust];
	
	$min_clust_obj = $row_cluster_dictionary[$min_clust];
	$max_clust_obj = $row_cluster_dictionary[$max_clust];

	if ($min_clust_obj->CC >= 2 && $max_clust_obj->CC >=2)
	{

		$min_min = min($min_clust_obj->C[0], $min_clust_obj->C[1]);
		$min_max = max($min_clust_obj->C[0], $min_clust_obj->C[1]);
		$max_min = min($max_clust_obj->C[0], $max_clust_obj->C[1]);
		$max_max = max($max_clust_obj->C[0], $max_clust_obj->C[1]);

		$min_d = $row_cluster_distance_dictionary[$min_min][$min_max];
		$max_d = $row_cluster_distance_dictionary[$max_min][$max_max];

		if ($min_d < $max_d)
		{
			OrganizeRows($max_clust); OrganizeRows($min_clust);
		}
		else
		{
			OrganizeRows($min_clust); OrganizeRows($max_clust); 
		}
	}
	else
	{
		if($min_clust_obj->CC < 2 && $max_clust_obj->CC >=2)
		{
			OrganizeRows($max_clust); OrganizeRows($min_clust); 
		}
		if($min_clust_obj->CC >= 2 && $max_clust_obj->CC <2)
		{
			OrganizeRows($min_clust); OrganizeRows($max_clust);
		}
		if($min_clust_obj->CC < 2 && $max_clust_obj->CC <2)
		{
			OrganizeRows($min_clust); OrganizeRows($max_clust);
		}
	}
}

function OrganizeColumns($currentID)
{
	global $column_cluster_dictionary;
	global $index_to_condition_id_dictionary;
	global $condition_index;
	global $column_cluster_distance_dictionary;

	$cluster_obj = $column_cluster_dictionary[$currentID];
	if ($cluster_obj->MID!=="")
	{
		//$index_to_condition_id_dictionary[$condition_index] = $cluster_obj->MID;
		$cluster_obj->x=$condition_index;
		$cluster_obj->y= 1;
		$condition_index++;
		return; 
	}
	$min_clust = min($cluster_obj->C[0], $cluster_obj->C[1]);
	$max_clust = max($cluster_obj->C[0], $cluster_obj->C[1]);
	
	$min_clust_obj = $column_cluster_dictionary[$min_clust];
	$max_clust_obj = $column_cluster_dictionary[$max_clust];
	$cluster_obj->d = $column_cluster_distance_dictionary[$min_clust][$max_clust];

	if ($min_clust_obj->CC >= 2 && $max_clust_obj->CC >=2)
	{

		$min_min = min($min_clust_obj->C[0], $min_clust_obj->C[1]);
		$min_max = max($min_clust_obj->C[0], $min_clust_obj->C[1]);
		$max_min = min($max_clust_obj->C[0], $max_clust_obj->C[1]);
		$max_max = max($max_clust_obj->C[0], $max_clust_obj->C[1]);

		$min_d = $column_cluster_distance_dictionary[$min_min][$min_max];
		$max_d = $column_cluster_distance_dictionary[$max_min][$max_max];

		if ($min_d < $max_d)
		{
			OrganizeColumns($min_clust); OrganizeColumns($max_clust);
		}
		else
		{
			 OrganizeColumns($min_clust); OrganizeColumns($max_clust);
		}
	}
	else
	{
		OrganizeColumns($min_clust); OrganizeColumns($max_clust);
	}
}

function DoClustering($linkage, $distanceCalc, $isRow, $analysisID, $db)
{

	global $current_cluster_id;
	global $row_cluster_dictionary;
	global $column_cluster_dictionary;
	global $row_dictionary;
	global $cycles;

	$initialClusters = array();
	if ($isRow)
	{
		$initialClusters = array_keys($row_cluster_dictionary);
	}
	else
	{
		$initialClusters = array_keys($column_cluster_dictionary);
	}

	$stack = new Stack(count($initialClusters) * 10, array());

	while (true) {
		if (count($initialClusters)==2 && $stack->count()==0)
		{
			//break out
			$newCluster = new Cluster();
			$newCluster->CID = $current_cluster_id;
			$newCluster->t = true;
			array_push($newCluster->C, $initialClusters[0]); array_push($newCluster->C, $initialClusters[1]);
			if($isRow)
			{
				$newCluster->CC = $row_cluster_dictionary[$initialClusters[0]]->CC + $row_cluster_dictionary[$initialClusters[1]]->CC;
				$row_cluster_dictionary[$current_cluster_id] = $newCluster;
			}
			else
			{
				$newCluster->CC = $column_cluster_dictionary[$initialClusters[0]]->CC + $column_cluster_dictionary[$initialClusters[1]]->CC;
				$column_cluster_dictionary[$current_cluster_id] = $newCluster;
			}
			$curr_distance = CalculateClusterDistance($initialClusters[0], $initialClusters[1], $linkage, $distanceCalc, $isRow);
			if (!CheckForEntry($initialClusters[0], $initialClusters[1], $isRow))
			{
				AddDistanceToDictionary($initialClusters[0], $initialClusters[1], $curr_distance, $isRow);
			}
			$current_cluster_id++;
			break;
		}
		if (count($initialClusters)==0 && $stack->count()==2)
		{
			//break out
			$newCluster = new Cluster();
			$newCluster->CID = $current_cluster_id;
			$cluster1 = $stack->pop();
			$cluster2 = $stack->pop();
			$newCluster->t = true;
			array_push($newCluster->C,$cluster1); array_push($newCluster->C, $cluster2);
			if($isRow)
			{
				$newCluster->CC = $row_cluster_dictionary[$cluster1]->CC + $row_cluster_dictionary[$cluster2]->CC;
				$row_cluster_dictionary[$current_cluster_id] = $newCluster;
			}
			else
			{
				$newCluster->CC = $column_cluster_dictionary[$cluster1]->CC + $column_cluster_dictionary[$cluster2]->CC;
				$column_cluster_dictionary[$current_cluster_id] = $newCluster;
			}
			$curr_distance = CalculateClusterDistance($cluster1, $cluster2, $linkage, $distanceCalc, $isRow);
			if (!CheckForEntry($cluster1, $cluster2, $isRow))
			{
				AddDistanceToDictionary($cluster1, $cluster2, $curr_distance, $isRow);
			}
			$current_cluster_id++;
			break;
		}

		$activeCluster = -1;

		if ($stack->count()==0)
		{
			$seedKey = rand(0, count($initialClusters)-1);
			$seedValue = $initialClusters[$seedKey];
			$stack->push($seedValue);
			array_splice($initialClusters, $seedKey, 1);
			$cycles++;
			if($cycles%20===0)
			{
				UpdateProgress($analysisID, $db);
			}
		}

		$activeCluster = $stack->top();

		$closestNeighbor = -1;
		$closestNeighborIndex = -1;
		$distance = 10000000; //arbitrarily large value
		$count = count($initialClusters);
		for ($i = 0; $i < $count; $i++)
		{
			$otherCluster = $initialClusters[$i];
			$currDistance = CalculateClusterDistance($activeCluster, $otherCluster, $linkage, $distanceCalc, $isRow);
			if ($currDistance < $distance)
			{
				$distance = $currDistance;
				$closestNeighbor = $otherCluster;
				$closestNeighborIndex = $i;
			}
		}

		$lastStackEvent = false;

		if ($stack->count()>1)
		{
			$last = $stack->secondEntry();
			$currDistance = CalculateClusterDistance($activeCluster, $last, $linkage, $distanceCalc, $isRow);
			if($currDistance < $distance)
			{
				$distance = $currDistance;
				$closestNeighbor = $last;
				$stack->pop();
				$stack->pop();
				$newCluster = new Cluster();
				$newCluster->CID = $current_cluster_id;
				array_push($newCluster->C, $activeCluster);
				array_push($newCluster->C, $closestNeighbor);
				if($isRow)
				{
					$newCluster->CC = $row_cluster_dictionary[$activeCluster]->CC + $row_cluster_dictionary[$closestNeighbor]->CC;
					$row_cluster_dictionary[$current_cluster_id] = $newCluster;
				}
				else
				{
					$newCluster->CC = $column_cluster_dictionary[$activeCluster]->CC + $column_cluster_dictionary[$closestNeighbor]->CC;
					$column_cluster_dictionary[$current_cluster_id] = $newCluster;
				}
				array_push($initialClusters, $current_cluster_id);
				$current_cluster_id++;
				$lastStackEvent = true;
			}
		}

		if(!$lastStackEvent)
		{
			$stack->push($closestNeighbor);
			array_splice($initialClusters, $closestNeighborIndex, 1);
			$cycles++;
			if($cycles%20===0)
			{
				UpdateProgress($analysisID, $db);
			}
		}
	}
}

function UpdateProgress($analysisID, $db)
{
	global $cycles;
	global $expectedCycles;
	global $percentComplete;

	$percentComplete = (int)(($cycles/$expectedCycles)*100);
	$query = "UPDATE hierarchical_clustering_inputs SET progress=:progress WHERE analysis_id=:analysis_id";
	$query_params = array(':progress'=>$percentComplete, ':analysis_id'=>$analysisID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

}

function CalculateClusterDistance($activeCluster, $otherCluster, $linkage, $distanceCalc, $isRow)
{
	global $row_cluster_distance_dictionary;
	global $column_cluster_distance_dictionary;
	if($isRow)
	{
		switch($linkage)
		{
			case 1: CalculateAverageDistanceBetweenRowClusters($activeCluster, $otherCluster, $distanceCalc, $isRow); break; //average
			case 2: CalculateCompleteDistanceBetweenRowClusters($activeCluster, $otherCluster, $distanceCalc, $isRow); break; //complete
			case 3: CalculateSingleDistanceBetweenRowClusters($activeCluster, $otherCluster, $distanceCalc, $isRow); break; //single
		}
		return $activeCluster < $otherCluster ? $row_cluster_distance_dictionary[$activeCluster][$otherCluster] : $row_cluster_distance_dictionary[$otherCluster][$activeCluster];
	}
	else
	{
		switch($linkage)
		{
			case 1: CalculateAverageDistanceBetweenColumnClusters($activeCluster, $otherCluster, $distanceCalc, $isRow); break; //average
			case 2: CalculateCompleteDistanceBetweenColumnClusters($activeCluster, $otherCluster, $distanceCalc, $isRow); break; //complete
			case 3: CalculateSingleDistanceBetweenColumnClusters($activeCluster, $otherCluster, $distanceCalc, $isRow); break; //single
		}
		return $activeCluster < $otherCluster ? $column_cluster_distance_dictionary[$activeCluster][$otherCluster] : $column_cluster_distance_dictionary[$otherCluster][$activeCluster];
	}
}

function CalculateAverageDistanceBetweenRowClusters($activeCluster, $otherCluster, $distanceCalc, $isRow)
{
	global $row_cluster_dictionary;
	global $row_dictionary;
	global $row_cluster_distance_dictionary;
	if (CheckForEntry($activeCluster, $otherCluster, $isRow))
	{
		return;
	}
	$activeClusterObj = $row_cluster_dictionary[$activeCluster];
	$otherClusterObj = $row_cluster_dictionary[$otherCluster];

	if($activeClusterObj->CC==1 && $otherClusterObj->CC==1)
	{
		$curr_distance = -1.0;
		switch($distanceCalc)
		{
			case 1: $curr_distance = CalculateEuclideanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;  //1-Euclidean, 2-Canberra, 3-Cosine, 4-Manhattan, 5-Maximum, 6-Pearson, 7-Spearman
			case 2: $curr_distance = CalculateCanberraDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 3: $curr_distance = CalculateCosineDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 4: $curr_distance = CalculateManhattanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 5: $curr_distance = CalculateMaximumDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 6: $curr_distance = CalculatePearsonDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 7: $curr_distance = CalculateSpearmanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
		}
		AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		return;
	}

	if($activeClusterObj->CC==1 || $otherClusterObj->CC==1)
	{
		if ($activeClusterObj->CC==1)
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateAverageDistanceBetweenRowClusters($activeCluster, $component, $distanceCalc, true);
			}
			$curr_distance = 0.0;
			$total_weights = 0.0;
			foreach ($otherClusterObj->C as $component) {
				$weight = $row_cluster_dictionary[$component]->CC;
				$total_weights += $weight;
				$component < $activeCluster ? $curr_distance += ($weight * $row_cluster_distance_dictionary[$component][$activeCluster]) : $curr_distance += ($weight * $row_cluster_distance_dictionary[$activeCluster][$component]);
			}
			$curr_distance /= $total_weights;
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		else
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateAverageDistanceBetweenRowClusters($otherCluster, $component, $distanceCalc, true);
			}
			$curr_distance = 0.0;
			$total_weights = 0.0;
			foreach ($activeClusterObj->C as $component) {
				$weight = $row_cluster_dictionary[$component]->CC;
				$total_weights += $weight;
				$component < $otherCluster ? $curr_distance += ($weight * $row_cluster_distance_dictionary[$component][$otherCluster]) : $curr_distance += ($weight * $row_cluster_distance_dictionary[$otherCluster][$component]);
			}
			$curr_distance /= $total_weights;
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		return;
	}

	if ($activeClusterObj->CC > 1 && $otherClusterObj->CC > 1)
	{
		if($activeCluster > $otherCluster) //Means that activeCluster was updated (read: merged) more recently than otherCluster
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateAverageDistanceBetweenRowClusters($component, $otherCluster, $distanceCalc, true);
			}
			$curr_distance = 0.0;
			$total_weights = 0.0;
			foreach ($activeClusterObj->C as $component) {
				$weight = $row_cluster_dictionary[$component]->CC;
				$total_weights += $weight;
				$component < $otherCluster ? $curr_distance += ($weight * $row_cluster_distance_dictionary[$component][$otherCluster]) : $curr_distance += ($weight * $row_cluster_distance_dictionary[$otherCluster][$component]);
			}
			$curr_distance /= $total_weights;
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		else
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateAverageDistanceBetweenRowClusters($component, $activeCluster, $distanceCalc, true);
			}
			$curr_distance = 0.0;
			$total_weights = 0.0;
			foreach ($otherClusterObj->C as $component) {
				$weight = $row_cluster_dictionary[$component]->CC;
				$total_weights += $weight;
				$component < $activeCluster ? $curr_distance += ($weight * $row_cluster_distance_dictionary[$component][$activeCluster]) : $curr_distance += ($weight * $row_cluster_distance_dictionary[$activeCluster][$component]);
			}
			$curr_distance /= $total_weights;
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
	}
}

function CalculateSingleDistanceBetweenRowClusters($activeCluster, $otherCluster, $distanceCalc, $isRow)
{
	global $row_cluster_dictionary;
	global $row_dictionary;
	global $row_cluster_distance_dictionary;
	if (CheckForEntry($activeCluster, $otherCluster, $isRow))
	{
		return;
	}
	$activeClusterObj = $row_cluster_dictionary[$activeCluster];
	$otherClusterObj = $row_cluster_dictionary[$otherCluster];

	if($activeClusterObj->CC==1 && $otherClusterObj->CC==1)
	{
		$curr_distance = -1.0;
		switch($distanceCalc)
		{
			case 1: $curr_distance = CalculateEuclideanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;  //1-Euclidean, 2-Canberra, 3-Cosine, 4-Manhattan, 5-Maximum, 6-Pearson, 7-Spearman
			case 2: $curr_distance = CalculateCanberraDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 3: $curr_distance = CalculateCosineDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 4: $curr_distance = CalculateManhattanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 5: $curr_distance = CalculateMaximumDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 6: $curr_distance = CalculatePearsonDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 7: $curr_distance = CalculateSpearmanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
		}
		AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		return;
	}

	if($activeClusterObj->CC==1 || $otherClusterObj->CC==1)
	{
		if ($activeClusterObj->CC==1)
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateSingleDistanceBetweenRowClusters($activeCluster, $component, $distanceCalc, true);
			}
			$curr_distance = INF;
			foreach ($otherClusterObj->C as $component) {
				//$compDist = 0.0;
				$component < $activeCluster ? $compDist = $row_cluster_distance_dictionary[$component][$activeCluster] : $compDist = $row_cluster_distance_dictionary[$activeCluster][$component];
				$curr_distance = min($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		else
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateSingleDistanceBetweenRowClusters($otherCluster, $component, $distanceCalc, true);
			}
			$curr_distance = INF;
			foreach ($activeClusterObj->C as $component) {
				//$compDist = 0.0;
				$component < $otherCluster ? $compDist = $row_cluster_distance_dictionary[$component][$otherCluster] : $compDist = $row_cluster_distance_dictionary[$otherCluster][$component];
				$curr_distance = min($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		return;
	}

	if ($activeClusterObj->CC > 1 && $otherClusterObj->CC > 1)
	{
		if($activeCluster > $otherCluster) //Means that activeCluster was updated (read: merged) more recently than otherCluster
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateSingleDistanceBetweenRowClusters($component, $otherCluster, $distanceCalc, true);
			}
			$curr_distance = INF;
			foreach ($activeClusterObj->C as $component) {
				//$compDist = 0.0;
				$component < $otherCluster ? $compDist = $row_cluster_distance_dictionary[$component][$otherCluster] : $compDist = $row_cluster_distance_dictionary[$otherCluster][$component];
				$curr_distance = min($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		else
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateSingleDistanceBetweenRowClusters($component, $activeCluster, $distanceCalc, true);
			}
			$curr_distance = INF;
			foreach ($otherClusterObj->C as $component) {
				//$compDist = 0.0;
				$component < $activeCluster ? $compDist = $row_cluster_distance_dictionary[$component][$activeCluster] : $compDist = $row_cluster_distance_dictionary[$activeCluster][$component];
				$curr_distance = min($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
	}
}

function CalculateCompleteDistanceBetweenRowClusters($activeCluster, $otherCluster, $distanceCalc, $isRow)
{
	global $row_cluster_dictionary;
	global $row_dictionary;
	global $row_cluster_distance_dictionary;
	if (CheckForEntry($activeCluster, $otherCluster, $isRow))
	{
		return;
	}
	$activeClusterObj = $row_cluster_dictionary[$activeCluster];
	$otherClusterObj = $row_cluster_dictionary[$otherCluster];

	if($activeClusterObj->CC==1 && $otherClusterObj->CC==1)
	{
		$curr_distance = -1.0;
		switch($distanceCalc)
		{
			case 1: $curr_distance = CalculateEuclideanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;  //1-Euclidean, 2-Canberra, 3-Cosine, 4-Manhattan, 5-Maximum, 6-Pearson, 7-Spearman
			case 2: $curr_distance = CalculateCanberraDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 3: $curr_distance = CalculateCosineDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 4: $curr_distance = CalculateManhattanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 5: $curr_distance = CalculateMaximumDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 6: $curr_distance = CalculatePearsonDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
			case 7: $curr_distance = CalculateSpearmanDistance($row_dictionary[$activeClusterObj->MID], $row_dictionary[$otherClusterObj->MID]); break;
		}
		AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		return;
	}

	if($activeClusterObj->CC==1 || $otherClusterObj->CC==1)
	{
		if ($activeClusterObj->CC==1)
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateCompleteDistanceBetweenRowClusters($activeCluster, $component, $distanceCalc, true);
			}
			$curr_distance = -INF;
			foreach ($otherClusterObj->C as $component) {
				$compDist = 0.0;
				$component < $activeCluster ? $compDist = $row_cluster_distance_dictionary[$component][$activeCluster] : $compDist = $row_cluster_distance_dictionary[$activeCluster][$component];
				$curr_distance = max($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		else
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateCompleteDistanceBetweenRowClusters($otherCluster, $component, $distanceCalc, true);
			}
			$curr_distance = -INF;
			foreach ($activeClusterObj->C as $component) {
				$compDist = 0.0;
				$component < $otherCluster ? $compDist = $row_cluster_distance_dictionary[$component][$otherCluster] : $compDist = $row_cluster_distance_dictionary[$otherCluster][$component];
				$curr_distance = max($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		return;
	}

	if ($activeClusterObj->CC > 1 && $otherClusterObj->CC > 1)
	{
		if($activeCluster > $otherCluster) //Means that activeCluster was updated (read: merged) more recently than otherCluster
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateCompleteDistanceBetweenRowClusters($component, $otherCluster, $distanceCalc, true);
			}
			$curr_distance = -INF;
			foreach ($activeClusterObj->C as $component) {
				$compDist = 0.0;
				$component < $otherCluster ? $compDist = $row_cluster_distance_dictionary[$component][$otherCluster] : $compDist = $row_cluster_distance_dictionary[$otherCluster][$component];
				$curr_distance = max($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
		else
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateCompleteDistanceBetweenRowClusters($component, $activeCluster, $distanceCalc, true);
			}
			$curr_distance = -INF;
			foreach ($otherClusterObj->C as $component) {
				$compDist = 0.0;
				$component < $activeCluster ? $compDist = $row_cluster_distance_dictionary[$component][$activeCluster] : $compDist = $row_cluster_distance_dictionary[$activeCluster][$component];
				$curr_distance = max($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, true);
		}
	}
}

function CalculateAverageDistanceBetweenColumnClusters($activeCluster, $otherCluster, $distanceCalc, $isRow)
{
	global $column_cluster_dictionary;
	global $column_dictionary;
	global $column_cluster_distance_dictionary;
	if (CheckForEntry($activeCluster, $otherCluster, $isRow))
	{
		return;
	}
	$activeClusterObj = $column_cluster_dictionary[$activeCluster];
	$otherClusterObj = $column_cluster_dictionary[$otherCluster];

	if($activeClusterObj->CC==1 && $otherClusterObj->CC==1)
	{
		$curr_distance = -1.0;
		switch($distanceCalc)
		{
			case 1: $curr_distance = CalculateEuclideanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;  //1-Euclidean, 2-Canberra, 3-Cosine, 4-Manhattan, 5-Maximum, 6-Pearson, 7-Spearman
			case 2: $curr_distance = CalculateCanberraDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 3: $curr_distance = CalculateCosineDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 4: $curr_distance = CalculateManhattanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 5: $curr_distance = CalculateMaximumDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 6: $curr_distance = CalculatePearsonDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 7: $curr_distance = CalculateSpearmanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
		}
		AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		return;
	}

	if($activeClusterObj->CC==1 || $otherClusterObj->CC==1)
	{
		if ($activeClusterObj->CC==1)
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateAverageDistanceBetweenColumnClusters($activeCluster, $component, $distanceCalc, false);
			}
			$curr_distance = 0.0;
			$total_weights = 0.0;
			foreach ($otherClusterObj->C as $component) {
				$weight = $column_cluster_dictionary[$component]->CC;
				$total_weights += $weight;
				$component < $activeCluster ? $curr_distance += ($weight * $column_cluster_distance_dictionary[$component][$activeCluster]) : $curr_distance += ($weight * $column_cluster_distance_dictionary[$activeCluster][$component]);
			}
			$curr_distance /= $total_weights;
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		else
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateAverageDistanceBetweenColumnClusters($otherCluster, $component, $distanceCalc, false);
			}
			$curr_distance = 0.0;
			$total_weights = 0.0;
			foreach ($activeClusterObj->C as $component) {
				$weight = $column_cluster_dictionary[$component]->CC;
				$total_weights += $weight;
				$component < $otherCluster ? $curr_distance += ($weight * $column_cluster_distance_dictionary[$component][$otherCluster]) : $curr_distance += ($weight * $column_cluster_distance_dictionary[$otherCluster][$component]);
			}
			$curr_distance /= $total_weights;
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		return;
	}

	if ($activeClusterObj->CC > 1 && $otherClusterObj->CC > 1)
	{
		if($activeCluster > $otherCluster) //Means that activeCluster was updated (read: merged) more recently than otherCluster
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateAverageDistanceBetweenColumnClusters($component, $otherCluster, $distanceCalc, false);
			}
			$curr_distance = 0.0;
			$total_weights = 0.0;
			foreach ($activeClusterObj->C as $component) {
				$weight = $column_cluster_dictionary[$component]->CC;
				$total_weights += $weight;
				$component < $otherCluster ? $curr_distance += ($weight * $column_cluster_distance_dictionary[$component][$otherCluster]) : $curr_distance += ($weight * $column_cluster_distance_dictionary[$otherCluster][$component]);
			}
			$curr_distance /= $total_weights;
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		else
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateAverageDistanceBetweenColumnClusters($component, $activeCluster, $distanceCalc, false);
			}
			$curr_distance = 0.0;
			$total_weights = 0.0;
			foreach ($otherClusterObj->C as $component) {
				$weight = $column_cluster_dictionary[$component]->CC;
				$total_weights += $weight;
				$component < $activeCluster ? $curr_distance += ($weight * $column_cluster_distance_dictionary[$component][$activeCluster]) : $curr_distance += ($weight * $column_cluster_distance_dictionary[$activeCluster][$component]);
			}
			$curr_distance /= $total_weights;
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
	}
}

function CalculateSingleDistanceBetweenColumnClusters($activeCluster, $otherCluster, $distanceCalc, $isRow)
{
	global $column_cluster_dictionary;
	global $column_dictionary;
	global $column_cluster_distance_dictionary;
	if (CheckForEntry($activeCluster, $otherCluster, $isRow))
	{
		return;
	}
	$activeClusterObj = $column_cluster_dictionary[$activeCluster];
	$otherClusterObj = $column_cluster_dictionary[$otherCluster];

	if($activeClusterObj->CC==1 && $otherClusterObj->CC==1)
	{
		$curr_distance = -1.0;
		switch($distanceCalc)
		{
			case 1: $curr_distance = CalculateEuclideanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;  //1-Euclidean, 2-Canberra, 3-Cosine, 4-Manhattan, 5-Maximum, 6-Pearson, 7-Spearman
			case 2: $curr_distance = CalculateCanberraDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 3: $curr_distance = CalculateCosineDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 4: $curr_distance = CalculateManhattanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 5: $curr_distance = CalculateMaximumDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 6: $curr_distance = CalculatePearsonDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 7: $curr_distance = CalculateSpearmanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
		}
		AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		return;
	}

	if($activeClusterObj->CC==1 || $otherClusterObj->CC==1)
	{
		if ($activeClusterObj->CC==1)
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateSingleDistanceBetweenColumnClusters($activeCluster, $component, $distanceCalc, false);
			}
			$curr_distance = INF;
			foreach ($otherClusterObj->C as $component) {
				//$compDist = 0.0;
				$component < $activeCluster ? $compDist = $column_cluster_distance_dictionary[$component][$activeCluster] : $compDist = $column_cluster_distance_dictionary[$activeCluster][$component];
				$curr_distance = min($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		else
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateSingleDistanceBetweenColumnClusters($otherCluster, $component, $distanceCalc, false);
			}
			$curr_distance = INF;
			foreach ($activeClusterObj->C as $component) {
				//$compDist = 0.0;
				$component < $otherCluster ? $compDist = $column_cluster_distance_dictionary[$component][$otherCluster] : $compDist = $column_cluster_distance_dictionary[$otherCluster][$component];
				$curr_distance = min($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		return;
	}

	if ($activeClusterObj->CC > 1 && $otherClusterObj->CC > 1)
	{
		if($activeCluster > $otherCluster) //Means that activeCluster was updated (read: merged) more recently than otherCluster
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateSingleDistanceBetweenColumnClusters($component, $otherCluster, $distanceCalc, false);
			}
			$curr_distance = INF;
			foreach ($activeClusterObj->C as $component) {
				//$compDist = 0.0;
				$component < $otherCluster ? $compDist = $column_cluster_distance_dictionary[$component][$otherCluster] : $compDist = $column_cluster_distance_dictionary[$otherCluster][$component];
				$curr_distance = min($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		else
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateSingleDistanceBetweenColumnClusters($component, $activeCluster, $distanceCalc, false);
			}
			$curr_distance = INF;
			foreach ($otherClusterObj->C as $component) {
				//$compDist = 0.0;
				$component < $activeCluster ? $compDist = $column_cluster_distance_dictionary[$component][$activeCluster] : $compDist = $column_cluster_distance_dictionary[$activeCluster][$component];
				$curr_distance = min($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
	}
}

function CalculateCompleteDistanceBetweenColumnClusters($activeCluster, $otherCluster, $distanceCalc, $isRow)
{
	global $column_cluster_dictionary;
	global $column_dictionary;
	global $column_cluster_distance_dictionary;
	if (CheckForEntry($activeCluster, $otherCluster, $isRow))
	{
		return;
	}
	$activeClusterObj = $column_cluster_dictionary[$activeCluster];
	$otherClusterObj = $column_cluster_dictionary[$otherCluster];

	if($activeClusterObj->CC==1 && $otherClusterObj->CC==1)
	{
		$curr_distance = -1.0;
		switch($distanceCalc)
		{
			case 1: $curr_distance = CalculateEuclideanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;  //1-Euclidean, 2-Canberra, 3-Cosine, 4-Manhattan, 5-Maximum, 6-Pearson, 7-Spearman
			case 2: $curr_distance = CalculateCanberraDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 3: $curr_distance = CalculateCosineDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 4: $curr_distance = CalculateManhattanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 5: $curr_distance = CalculateMaximumDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 6: $curr_distance = CalculatePearsonDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
			case 7: $curr_distance = CalculateSpearmanDistance($column_dictionary[$activeClusterObj->MID], $column_dictionary[$otherClusterObj->MID]); break;
		}
		AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		return;
	}

	if($activeClusterObj->CC==1 || $otherClusterObj->CC==1)
	{
		if ($activeClusterObj->CC==1)
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateCompleteDistanceBetweenColumnClusters($activeCluster, $component, $distanceCalc, false);
			}
			$curr_distance = -INF;
			foreach ($otherClusterObj->C as $component) {
				$compDist = 0.0;
				$component < $activeCluster ? $compDist = $column_cluster_distance_dictionary[$component][$activeCluster] : $compDist = $column_cluster_distance_dictionary[$activeCluster][$component];
				$curr_distance = max($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		else
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateCompleteDistanceBetweenColumnClusters($otherCluster, $component, $distanceCalc, false);
			}
			$curr_distance = -INF;
			foreach ($activeClusterObj->C as $component) {
				$compDist = 0.0;
				$component < $otherCluster ? $compDist = $column_cluster_distance_dictionary[$component][$otherCluster] : $compDist = $column_cluster_distance_dictionary[$otherCluster][$component];
				$curr_distance = max($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		return;
	}

	if ($activeClusterObj->CC > 1 && $otherClusterObj->CC > 1)
	{
		if($activeCluster > $otherCluster) //Means that activeCluster was updated (read: merged) more recently than otherCluster
		{
			foreach ($activeClusterObj->C as $component) {
				CalculateCompleteDistanceBetweenColumnClusters($component, $otherCluster, $distanceCalc, false);
			}
			$curr_distance = -INF;
			foreach ($activeClusterObj->C as $component) {
				$compDist = 0.0;
				$component < $otherCluster ? $compDist = $column_cluster_distance_dictionary[$component][$otherCluster] : $compDist = $column_cluster_distance_dictionary[$otherCluster][$component];
				$curr_distance = max($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
		else
		{
			foreach ($otherClusterObj->C as $component) {
				CalculateCompleteDistanceBetweenColumnClusters($component, $activeCluster, $distanceCalc, false);
			}
			$curr_distance = -INF;
			foreach ($otherClusterObj->C as $component) {
				$compDist = 0.0;
				$component < $activeCluster ? $compDist = $column_cluster_distance_dictionary[$component][$activeCluster] : $compDist = $column_cluster_distance_dictionary[$activeCluster][$component];
				$curr_distance = max($curr_distance, $compDist);
			}
			AddDistanceToDictionary($activeCluster, $otherCluster, $curr_distance, false);
		}
	}
}

//Helper functions
function CreateInitialClusters($dictionary)
{
	global $current_cluster_id;
	$return_dictionary = array();
	foreach ($dictionary as $key => $value) {
		$new_cluster = new Cluster();
		$new_cluster->CID = $current_cluster_id;
		$new_cluster->CC = 1;
		$new_cluster->MID = $key;
		$return_dictionary[$current_cluster_id] =  $new_cluster;
		$current_cluster_id++;
	}
	return $return_dictionary;
}

function AddDistanceToDictionary($activeCluster, $otherCluster, $distance, $isRow)
{
	global $row_cluster_distance_dictionary;
	global $column_cluster_distance_dictionary;
	if ($isRow)
	{
		if ($activeCluster < $otherCluster)
		{
			if(!array_key_exists($activeCluster, $row_cluster_distance_dictionary))
			{
				$row_cluster_distance_dictionary[$activeCluster] = array();
			}
			$row_cluster_distance_dictionary[$activeCluster][$otherCluster] = $distance;
		}
		else
		{
			if(!array_key_exists($otherCluster, $row_cluster_distance_dictionary))
			{
				$row_cluster_distance_dictionary[$otherCluster] = array();
			}
			$row_cluster_distance_dictionary[$otherCluster][$activeCluster] = $distance;
		}
	}
	else
	{
		if ($activeCluster < $otherCluster)
		{
			if(!array_key_exists($activeCluster, $column_cluster_distance_dictionary))
			{
				$column_cluster_distance_dictionary[$activeCluster] = array();
			}
			$column_cluster_distance_dictionary[$activeCluster][$otherCluster] = $distance;
		}
		else
		{
			if(!array_key_exists($otherCluster, $column_cluster_distance_dictionary))
			{
				$column_cluster_distance_dictionary[$otherCluster] = array();
			}
			$column_cluster_distance_dictionary[$otherCluster][$activeCluster] = $distance;
		}
	}
}

function CheckForEntry($activeCluster, $otherCluster, $isRow)
{
	global $row_cluster_distance_dictionary;
	global $column_cluster_distance_dictionary;
	if($isRow)
	{
		if ($activeCluster < $otherCluster)
		{
			if(array_key_exists($activeCluster, $row_cluster_distance_dictionary))
			{
				return array_key_exists($otherCluster, $row_cluster_distance_dictionary[$activeCluster]);
			}
		}
		else
		{
			if (array_key_exists($otherCluster, $row_cluster_distance_dictionary))
			{
				return array_key_exists($activeCluster, $row_cluster_distance_dictionary[$otherCluster]);
			}
		}
	}
	else
	{
		if ($activeCluster < $otherCluster)
		{
			if(array_key_exists($activeCluster, $column_cluster_distance_dictionary))
			{
				return array_key_exists($otherCluster, $column_cluster_distance_dictionary[$activeCluster]);
			}
		}
		else
		{
			if (array_key_exists($otherCluster, $column_cluster_distance_dictionary))
			{
				return array_key_exists($activeCluster, $column_cluster_distance_dictionary[$otherCluster]);
			}
		}
	}
	return false;
}

function GetRowDictionary($original_2d_array)
{
	$return_array = array();
	$cond_count = count($original_2d_array);
	foreach ($original_2d_array as $key => $value) {
		foreach ($value as $mol_id => $fc) {
			if (!array_key_exists($mol_id, $return_array))
			{
				$return_array[$mol_id] = array();
			}
			array_push($return_array[$mol_id], $fc);
		}
	}
	foreach ($return_array as $key => $value) {
		if (count($value)!=$cond_count)
		{
			unset($return_array[$key]);
		}
	}
	return $return_array;
}
function GetColumnDictionary($original_2d_array, $row_dictionary)
{
	$return_array = array();
	foreach ($original_2d_array as $cond_id => $cond_array) {
		$return_array[$cond_id] = array();
		foreach ($row_dictionary as $mol_id => $mol_array) {
			array_push($return_array[$cond_id], $original_2d_array[$cond_id][$mol_id]);
		}
	}
	return $return_array;
}

//Distance calculation functions
function CalculateEuclideanDistance($array_1, $array_2)
{
	$diff = 0.0;
	$length = count($array_1);
	for ($i = 0; $i < $length; $i++)
	{
		$diff += pow(abs($array_1[$i]-$array_2[$i]), 2);
	}
	$diff = sqrt($diff);
	return $diff;
}

function CalculateManhattanDistance($array_1, $array_2)
{
	$diff = 0.0;
	$length = count($array_1);
	for ($i = 0; $i < $length; $i++)
	{
		$diff += abs($array_1[$i]-$array_2[$i]);
	}
	return $diff;
}

function CalculateMaximumDistance($array_1, $array_2)
{
	$diff = -1.0;
	$length = count($array_1);
	for ($i = 0; $i < $length; $i++)
	{
		$diff = max($diff, abs($array_1[$i]-$array_2[$i]));
	}
	return $diff;
}

function CalculatePearsonDistance($array_1, $array_2)
{

	$length = count($array_1);
	$a1M = Mean($array_1);
	$a2M = Mean($array_2);
	$numTerm = 0;
	$denomTerm1 = 0;
	$denomTerm2 = 0;
	for ($i = 0; $i < $length; $i++)
	{
		$numTerm += (($array_1[$i]-$a1M)*($array_2[$i]-$a2M));
		$denomTerm1 += pow(($array_1[$i]-$a1M),2);
		$denomTerm2 += pow(($array_2[$i]-$a2M),2);
	}
	$denomTerm = sqrt($denomTerm1) * sqrt($denomTerm2);
	$r = $numTerm/$denomTerm;
	return 1-$r;

}

function CalculateCosineDistance($array_1, $array_2)
{
	$numTerm = 0.0;
	$denomA = 0.0;
	$denomB = 0.0;
	$length = count($array_1);
	for ($i=0; $i < $length; $i++)
	{
		$numTerm += ($array_1[$i] * $array_2[$i]);
		$denomA += pow(abs($array_1[$i]), 2);
		$denomB += pow(abs($array_2[$i]), 2);
	}
	$val = ($numTerm/(sqrt($denomA) * sqrt($denomB)));

	return 1-$val;
}

function CalculateCanberraDistance($array_1, $array_2)
{
	$diff = 0.0;
	$length = count($array_1);
	for ($i = 0; $i < $length; $i++)
	{
		$diff += (abs($array_1[$i]-$array_2[$i]) / (abs($array_1[$i])+abs($array_2[$i])));
	}
	return $diff;
}

function CalculateSpearmanDistance($array_1, $array_2)
{
	$array_1_copy = $array_1;
	$array_2_copy = $array_2;

	sort($array_1_copy);
	sort($array_2_copy);

	$array_1_dict = array();
	$array_2_dict = array();

	$length = count($array_1);

	for ($i = 0; $i < $length; $i++)
	{
		$array_1_dict[$array_1_copy[$i] * 100000] = $i+1;
		$array_2_dict[$array_2_copy[$i] * 100000] = $i+1;
	}

	$num = 0.0;
	for ($i = 0; $i < $length; $i++)
	{
		$_1rank = $array_1_dict[$array_1[$i]*100000];
		$_2rank = $array_2_dict[$array_2[$i]*100000];
		$val = pow(($_1rank-$_2rank),2);
		$num += $val;
	}
	$num *=6;
	$denom = ($length * (pow($length,2)-1));
	$val = (1.0-($num/$denom));
	//$val *=-1;
	/*if($val >=0)
	{
		return $val;
	}
	else
	{
		return 1 + abs($val);
	}*/
	return 1-$val;
}

function standard_deviation($array) {
	if (count($array)<=1)
	{
		return 0;
	}
    // square root of sum of squares devided by N-1
    return sqrt(array_sum(array_map("sd_square", $array, array_fill(0,count($array), (array_sum($array) / count($array)) ) ) ) / (count($array)-1) );
}
function sd_square($x, $mean) { return pow($x - $mean,2); }
function Mean($arr)
{
	if (count($arr)==0)
	{
		return 0;
	}
	return array_sum($arr)/count($arr);
}
class Cluster
{
	public $CID=-1;
	public $MID="";
	public $CC=0;
	public $C=array();
	public $x = 0;
	public $y = 0;
	public $d = 0;
	public $t = false;
}

class Stack {

    protected $stack;
    protected $limit;

    public function __construct($limit = 10, $initial = array()) {
        // initialize the stack
        $this->stack = $initial;
        // stack can only contain this many items
        $this->limit = $limit;
    }

    public function push($item) {
        // trap for stack overflow
        if (count($this->stack) < $this->limit) {
            // prepend item to the start of the array
            array_unshift($this->stack, $item);
        } else {
            throw new RunTimeException('Stack is full!');
        }
    }

    public function pop() {
        if ($this->isEmpty()) {
            // trap for stack underflow
            throw new RunTimeException('Stack is empty!');
        } else {
            // pop item from the start of the array
            return array_shift($this->stack);
        }
    }

    public function top() {
        //return current($this->stack);
         return ($this->stack[0]);
    }

    public function isEmpty() {
        return empty($this->stack);
    }

    public function count()
    {
    	return count($this->stack);
    }
     
     public function secondEntry()
     {
     	return ($this->stack[1]);
     }

     public function printStack()
     {
     	echo(json_encode($this->stack));
     }
}

class StopWatch {
	/**
	* @var $startTimes array The start times of the StopWatches
	*/
	private static $startTimes = array();
	/**
	* Start the timer
	*
	* @param $timerName string The name of the timer
	* @return void
	*/
	public static function start($timerName = 'default') {
		self::$startTimes[$timerName] = microtime(true);
	}
	/**
	* Get the elapsed time in seconds
	*
	* @param $timerName string The name of the timer to start
	* @return float The elapsed time since start() was called
	*/
	public static function elapsed($timerName = 'default') {
		return microtime(true) - self::$startTimes[$timerName];
	}
}
