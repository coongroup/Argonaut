<?php

require('config.php');

$analysis_id = $_POST['ai'];
$branch_id=$_POST['bi'];
$numClusters = $_POST['nc'];
$clusterID= $_POST['ci'];

$returnArray = array();

//check whether the analysis exists, 
$query = "SELECT completed FROM hierarchical_clustering_go_analysis WHERE analysis_id=:analysis_id AND cluster_count=:cluster_count AND project_id=:project_id";
$query_params = array(':analysis_id' => $analysis_id, ':cluster_count' => $numClusters, ':project_id' => $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

if(!$row)
{
	echo(json_encode(array()));
	exit();
}


if($row['completed']==="1" || $row['completed']===1)
{
	//this is good
	$allClusters = json_decode(file_get_contents($analysis_id . '_' . $numClusters . '_GOEnrich.txt'),true);

	$selectedClusterList = array_filter($allClusters, function($obj){
		global $clusterID;
		if((string)$obj['id']===(string)$clusterID)
		{
			return true;
		}
	});

	//echo(json_encode($selectedClusterList));

	if(count($selectedClusterList)===1)
	{
		$selectedCluster = $selectedClusterList[array_keys($selectedClusterList)[0]];
		//echo($selectedCluster['org']);


		$unique_id_to_standard_dict = array();

		$query = "SELECT DISTINCT unique_identifier_id, standard_molecule_id FROM data_condition_data WHERE branch_id=:branch_id AND standard_molecule_id!=-1";
		$query_params = array(':branch_id'=> $branch_id);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		foreach ($row as $entry) {
			$unique_id_to_standard_dict[$entry['unique_identifier_id']] = $entry['standard_molecule_id'];
		}

		$query = "SELECT a.go_term_id, a.all_molecule_ids, b.term, b.external_id, b.namespace FROM gene_ontology_multiple_term AS a JOIN gene_ontology AS b ON a.go_term_id=b.term_id WHERE a.organism_id=:organism";
		$query_params = array(':organism'=>$selectedCluster['org']);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();

		//for each enriched term you need to return an array (term_id, term_name, term_namespace, term_external, mol_list = array(mol_name, has_term), )

		$local_go_dict = array();
		foreach ($row as $entry) {
			$local_go_dict[$entry['go_term_id']] = array('term_id' => $entry['go_term_id'], 'term_name'=>$entry['term'], 'term_namespace' => $entry['namespace'], 
				'term_external' => $entry['external_id'], 'mol_list'=>array());
			$tmpIDs = explode(";", $entry['all_molecule_ids']);
			foreach ($tmpIDs as $id) {
				$local_go_dict[$entry['go_term_id']]['mol_list'][(string)$id]="";
			}
		}

		foreach ($selectedCluster['enriched'] as $entry) {
			$currObject = array('term_name'=> $local_go_dict[$entry['goID']]['term_name'], 'term_namespace'=> $local_go_dict[$entry['goID']]['term_namespace'],
				'term_external'=>$local_go_dict[$entry['goID']]['term_external'], 'p'=>$entry['pVal'], 'pFDR'=>$entry['pFDR'], 'pBonferroni'=> $entry['pBonf'], 
				'a'=>$entry['a'], 'b'=>$entry['b'], 'c'=>$entry['c'], 'd'=>$entry['d'], 'molList'=>array());

			foreach ($selectedCluster['molDict'] as $molID) {
				if(array_key_exists($molID, $unique_id_to_standard_dict))
				{
					$sID = $unique_id_to_standard_dict[$molID];
					if(array_key_exists((string)$sID, $local_go_dict[$entry['goID']]['mol_list']))
					{
						array_push($currObject['molList'], $molID . "_T");
					}
					else
					{
						array_push($currObject['molList'], $molID . "_F");
					}
				}
			}

			array_push($returnArray, $currObject);
		}

		usort($returnArray, "enrichSort");

		echo(json_encode($returnArray));
		//echo("here");
	}

}

function enrichSort($a, $b)
{
	if ($a['p'] === $b['p'])
	{
		return 0;
	}
	return $a['p']> $b['p'] ? 1 : -1;
}
