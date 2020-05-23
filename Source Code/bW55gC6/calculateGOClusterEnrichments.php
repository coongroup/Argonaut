<?php

require('config.php');

/*$analysis_id = $_POST['ai'];
$branch_id=$_POST['bi'];
$numClusters = $_POST['nc'];
*/

$projectID='bW55gC6';

while (true)
{
	$query = "SELECT a.analysis_id, a.cluster_count, b.branch_id FROM hierarchical_clustering_go_analysis AS a JOIN hierarchical_clustering_inputs AS b ON a.analysis_id=b.analysis_id WHERE a.completed=0 AND a.project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		exit();
	}

	$analysis_id = $row['analysis_id'];
	$branch_id=$row['branch_id'];
	$numClusters = $row['cluster_count'];

	StartProcessing($analysis_id, $db);

	$query = "SELECT row_clusters FROM hierarchical_clustering_results WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id' => $analysis_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();

	if (!$row)
	{
		//couldnt find the data on the server, return a message and break out
		exit();
	}

	//Create and order all row cluster objects  
	$rowClusters = json_decode($row['row_clusters'], true);

	$tmpRowClusters = array();
	usort($rowClusters, "clusterSort");
	$rowClusterCount = count($rowClusters);
	$threshold = $rowClusters[$numClusters-1]['d'];

	foreach ($rowClusters as $entry) {
		$tmpRowClusters[$entry['CID']] = $entry;
	}

	$rootNodes = array();
	$startNodeKey = $rowClusters[0]['CID'];

	TraverseTree($tmpRowClusters[$startNodeKey]);

	$allClusters = array();
	$allHeatMapMolIDs = array();

	foreach ($rootNodes as $entry) {
		$rangeArray = array();
		$molIDArray = array();
		AssembleRanges($tmpRowClusters[$entry], $rangeArray, $molIDArray);
		/*array_push($allClusters, array('min' => min($rangeArray), 'max' => max($rangeArray)))
		array_push($outData['clusterRanges'], min($rangeArray) . "_" . max($rangeArray));*/
		$newCluster = new Cluster();
		$newCluster->minY = min($rangeArray);
		$newCluster->maxY = max($rangeArray);
		$newCluster->molDict = $molIDArray;
		foreach ($molIDArray as $entry2) {
			array_push($allHeatMapMolIDs, $entry2);
		}

		array_push($allClusters, $newCluster);
	}

	usort($allClusters, "clusterObjSort");

	$currID = 1;
	foreach ($allClusters as $entry) {
		$entry->id=$currID;
		$currID++;
	}

	UpdateProgress($analysis_id, 10, $db);

	//assemble a dictionary of the format (unique_identifier_id -> standard_molecule_id)
	//straight up, you need to find a better way to do this. On the initial upload you should map molecules to their standard molecules and store that information 
	//in the data_unique_identifiers table.

	$unique_id_to_standard_dict = array();

	$query = "SELECT DISTINCT unique_identifier_id, standard_molecule_id FROM data_condition_data WHERE branch_id=:branch_id AND standard_molecule_id!=-1";
	$query_params = array(':branch_id'=> $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();
	foreach ($row as $entry) {
		$unique_id_to_standard_dict[$entry['unique_identifier_id']] = $entry['standard_molecule_id'];
	}


	//get organism
	$query = "SELECT organism_id, standard_id_type, standard_id_column, identifier FROM project_files WHERE branch_id=:branch_id AND organism_id!=-1";
	$query_params = array(':branch_id' => $branch_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	if(!$row)
	{
		//couldn't find an associated organism. Can't do the enrichment analysis.
		//return a message and break out.
		exit();
	}

	$branch_organism = -1;

	foreach ($row as $entry) {
		$branch_organism = $entry['organism_id'];
	}

	foreach ($allClusters as $currCluster) {
		$currCluster->org = $branch_organism;
		foreach ($currCluster->molDict as $molID) {
			if(array_key_exists($molID, $unique_id_to_standard_dict))
			{
				array_push($currCluster->standardMolDict, $unique_id_to_standard_dict[$molID]);
			}
		}
	}

	UpdateProgress($analysis_id, 20, $db);

	//echo(json_encode($allHeatMapMolIDs));

	$query = "SELECT all_molecule_ids, go_term_id FROM gene_ontology_multiple_term WHERE organism_id=:organism";
	$query_params = array(':organism' => $branch_organism);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	$totalTerms = count($row);
	$termsCompleted = 0;

	//for each go term...
	//using the master list of allheatmapmolids calculate figure out a hasTerm # and noTerm #
	//for each cluster find the clusterHasTerm # and clusterNoTerm # then subtract these from the larger numbers and calculate a p-value (assuming clusterHasTerm > 0)

	foreach ($row as $entry) {

		$allStandardIDs = explode(";", $entry['all_molecule_ids']);
		
		$tmpArray = array();
		foreach ($allStandardIDs as $id) {
			$tmpArray[(string)$id] = "";
		}
		$allHasTerm = 0;
		$allNoTerm = 0;

		foreach ($allHeatMapMolIDs as $molID) {
			if (array_key_exists($molID, $unique_id_to_standard_dict))
			{
				$standard_id = $unique_id_to_standard_dict[$molID];
				array_key_exists((string)$standard_id, $tmpArray) ? $allHasTerm++ : $allNoTerm++;
			}
		}
		
		foreach ($allClusters as $currCluster) {
			if (count($currCluster->standardMolDict)>1)
			{
				$clusterHasTerm = 0;
				$clusterNoTerm = 0;
				foreach ($currCluster->standardMolDict as $molID) {
					array_key_exists((string)$molID, $tmpArray) ? $clusterHasTerm++ : $clusterNoTerm++;
				}

				if ($clusterHasTerm >= 1)
				{
					$a = $clusterHasTerm;
					$b = $clusterNoTerm;
					$c = ($allHasTerm-$clusterHasTerm);
					$d = ($allNoTerm-$clusterNoTerm);
					$p = GetPValue($a, $b, $c, $d);
					if ($p < 0.05)
					{
						$currEnrich = new Enrichment();
						$currEnrich->a = $a;
						$currEnrich->b = $b;
						$currEnrich->c = $c;
						$currEnrich->d = $d;
						$currEnrich->pVal = $p;
						$currEnrich->goID = $entry['go_term_id'];
						array_push($currCluster->enriched, $currEnrich);
					}
				}
			}
		}

		$termsCompleted++;
		if($termsCompleted%10===0)
		{
			$currPercent = ((($termsCompleted)/$totalTerms)*80)+20;
			UpdateProgress($analysis_id, $currPercent, $db);
		}
	}

	GetAdjustedPValues($allClusters);
	$file_name = $analysis_id . "_" . $numClusters . "_GOEnrich.txt";
	$file = fopen($file_name, "w");
	fwrite($file, json_encode($allClusters));
	fclose($file);
	FinishProcessing($analysis_id, $db);


}

function StartProcessing($analysis_id, $db)
{
	$query = "UPDATE hierarchical_clustering_go_analysis SET running=1 WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id'=>$analysis_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

function FinishProcessing($analysis_id, $db)
{
	$query = "UPDATE hierarchical_clustering_go_analysis SET running=0, completed=1 WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id'=>$analysis_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

function UpdateProgress($analysis_id, $progress, $db)
{
	$query = "UPDATE hierarchical_clustering_go_analysis SET progress=:progress WHERE analysis_id=:analysis_id";
	$query_params = array(':progress'=>$progress, ':analysis_id'=>$analysis_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}

//Function adapted from GetFDRPValues in calculateGOEnrichments.php
function GetAdjustedPValues(&$allClusters)
{
	foreach ($allClusters as $cluster) {
		if (count($cluster->enriched)>1)
		{
			$control_p_value_array = array();
			foreach ($cluster->enriched as $enrichment) {
				array_push($control_p_value_array, $enrichment->pVal);
				$enrichment->pBonf = min((count($cluster->enriched) * $enrichment->pVal), 1);
			}
			rsort($control_p_value_array);
			$corrected_p_values_control = array();
			array_push($corrected_p_values_control, $control_p_value_array[0]);
			for ($i = 1; $i < count($control_p_value_array); $i++)
			{
				$coefficient = ((double)count($control_p_value_array))/((double)count($control_p_value_array)-(double)$i);
				$curr_p_value = $control_p_value_array[$i] * $coefficient;
				$last_p_value = end($corrected_p_values_control);
				array_push($corrected_p_values_control, min($curr_p_value, $last_p_value));
			}
			$control_p_value_mapping_array = array();
			for ($i = 0; $i<count($control_p_value_array); $i++)
			{
				$original_p_value = $control_p_value_array[$i];
				$corrected_p_value = $corrected_p_values_control[$i];
				if(!array_key_exists((string)$original_p_value, $control_p_value_mapping_array))
				{
					$control_p_value_mapping_array[(string)$original_p_value] = $corrected_p_value;
				}
			}
			foreach ($cluster->enriched as $enrichment) {
				$lookup = (string)$enrichment->pVal;
				$fdr_p_value = $control_p_value_mapping_array[$lookup];
				$enrichment->pFDR = $fdr_p_value;
			}
		}
		else
		{
			foreach ($cluster->enriched as $enrichment) {
				$enrichment->pFDR = $enrichment->pVal;
				$enrichment->pBonf = $enrichment->pVal;
			}
		}
	}
}


function clusterSort($a, $b)
{
	if ($a['d']===$b['d'])
	{
		return 0;
	}
	return $a['d'] > $b['d'] ? -1 : 1;
}

function clusterObjSort($a, $b)
{
	return $a->minY < $b->minY ? -1 : 1;
}

function TraverseTree($node)
{
	global $threshold;
	global $tmpRowClusters;
	global $rootNodes;

	if ($node['d'] <= $threshold)
	{
		array_push($rootNodes, $node['CID']);
	}
	else
	{
		TraverseTree($tmpRowClusters[$node['C'][0]]);
		TraverseTree($tmpRowClusters[$node['C'][1]]);
	}
}

function AssembleRanges($node, &$array, &$molIDArray)
{
	global $tmpRowClusters;
	if ($node['MID']==="")
	{
		AssembleRanges($tmpRowClusters[$node['C'][0]], $array, $molIDArray);
		AssembleRanges($tmpRowClusters[$node['C'][1]], $array, $molIDArray);
	}
	else
	{
		array_push($array, $node['y']);
		array_push($molIDArray, $node['MID']);
		return;
	}
}

//Fisher's Exact Test Code Here
function GetPValue($a, $b, $c, $d)
{
	$n = $a + $b + $c + $d;
	$a_b = $a + $b;
	$c_d = $c + $d;
	$a_c = $a + $c;
	$b_d = $b + $d;
	$numVals = array();
	$denomVals = array();
	AddToArray($numVals, $a_b);
	AddToArray($numVals, $c_d);
	AddToArray($numVals, $a_c);
	AddToArray($numVals, $b_d);
	AddToArray($denomVals, $a);
	AddToArray($denomVals, $b);
	AddToArray($denomVals, $c);
	AddToArray($denomVals, $d);
	AddToArray($denomVals, $n);
	SimplifyArrays($numVals, $denomVals);
	$numVal = CalculateProduct($numVals);
	$denomVal = CalculateProduct($denomVals);
	$pVal = bcdiv($numVal,$denomVal,12);
	return $pVal;
}

function AddToArray(&$array, $start)
{
	for ($i = $start; $i>=1; $i--)
	{
		if (!array_key_exists((string)$i,$array))
	{
		$array[(string)$i] = 0;
	}
	$array[(string)$i]++;
	}
}

function SimplifyArrays(&$numArray, &$denomArray)
{
	foreach ($numArray as $key => $numValue) {
		if (array_key_exists($key, $denomArray))
		{
			$denomVal = $denomArray[$key];
			if($denomArray[$key]===$numArray[$key])
			{
				$denomArray[$key]=0;
				$numArray[$key] = 0;
			}
			if($denomArray[$key]>$numArray[$key])
			{
				$denomArray[$key]-=$numArray[$key];
				$numArray[$key] = 0;
			}
			if($denomArray[$key]<$numArray[$key])
			{
				$numArray[$key]-=$denomArray[$key];
				$denomArray[$key]=0;
			}
		}
	}
}

function CalculateProduct($array)
{
	$returnVal = 1.0;
	foreach ($array as $key => $value) {
		$intKey = (int)$key;
		for ($i=0; $i < $value; $i++)
		{
			$returnVal = bcmul($returnVal, $intKey); 
		}
	}
	return $returnVal;
}

class Cluster
{
	public $id = -1;
	public $minY = -1;
	public $maxY = -1;
	public $molDict = array();
	public $standardMolDict = array();
	public $enriched = array();
	public $org = -1;
}

class Enrichment
{
	public $a = -1;
	public $b = -1;
	public $c = -1;
	public $d = -1;
	public $pVal = -1;
	public $pFDR = -1;
	public $pBonf = -1;
	public $goID = -1;
	//public $enrichment = -1;
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
