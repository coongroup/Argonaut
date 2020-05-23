<?php

require("config.php");

if(empty($_SESSION['user']))
{
   // header("Location: index.html");
    //die("Redirecting to index.html");
}

//$conditionID = 'HZHocxu-1C';
$conditionID =$_POST['ci'];
$query = "SELECT a.unique_identifier_id, b.go_term_id, c.term, c.namespace FROM data_descriptive_statistics AS a JOIN gene_ontology_single_term AS b ON a.standard_molecule_id=b.standard_molecule_id JOIN gene_ontology AS c ON b.go_term_id=c.term_id WHERE a.condition_id=:condition_id";
$query_params = array(':condition_id'=> $conditionID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

$data = array();
foreach($row as $entry)
{
	$go_term = $entry['term']; $term_id = $entry['go_term_id']; $unique_identifier_id = $entry['unique_identifier_id']; $namespace = $entry['namespace'];
	if(!array_key_exists($go_term, $data))
	{
		$data[$go_term] = array('term_id'=> $term_id, 'term' => $go_term, 'namespace' => $namespace, 'all_mol_ids'=>array(), 'matching_molecules'=>0);
	}
	array_push($data[$go_term]['all_mol_ids'], $unique_identifier_id); $data[$go_term]['matching_molecules']++;
}

usort($data, "cmp");
echo(json_encode($data));

function cmp($a, $b)
{
	return $b['matching_molecules']-$a['matching_molecules'];
}
