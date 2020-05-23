<?php
require("config.php");

$projectID='bW55gC6';

while (true)
{

	LockTable($db);
	//query any waiting tasks
	//$query = "SELECT * FROM process_queue WHERE project_id=:project_id AND completed=0 AND running=0 ORDER BY task_creation_time LIMIT 1"; //Use this here when not doing dev work
	$query = "SELECT * FROM go_enrichment_analysis_queue WHERE project_id=:project_id AND complete=0 ORDER BY task_creation_time LIMIT 1";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		//unlock the table and break out
		UnlockTable($db);
		break;
	}
	UnlockTable($db);
	$branch_id = $row['branch_id'];
	$start_condition_id = $row['condition_id'];
	$repeat = $row['repeat_process'];
	$process_id = $row['process_id'];
	$input_string = $row['input_string'];

	$input_string_parts = ParseInputString($input_string);
	$fcCutoff = $input_string_parts['fcCutoff'];
	$fcSymbol = $input_string_parts['fcSymbol'];
	$pValCutoff = $input_string_parts['pValCutoff'];
	$pValSymbol = $input_string_parts['pValSymbol'];

	$condition_id_array = GetConditionsFromBranch($branch_id, $db, $repeat, $start_condition_id, $projectID);

	$possible_progress = count($condition_id_array) * 10;
	$total_progress = 0;
	$curr_cond_count = 0;
	SetRunning($process_id, $db);

	foreach ($condition_id_array as $condition_id) {
		$curr_cond_count++;
		try
		{
			//Check that analysis has not yet been performed
			if(AnalysisPerformed($input_string, $condition_id, $projectID, $db))
			{
				continue;
			}

			//Get Organism Standard Molecules
			$standard_molecule_to_go_terms = GetStandardMoleculeGOTermArray($projectID, $condition_id, $db); $total_progress++; UpdateProgress($total_progress, $possible_progress, $process_id, $db);
			$standard_molecules = GetStandardMoleculeIDArray($projectID, $condition_id, $db);  $total_progress++; UpdateProgress($total_progress, $possible_progress, $process_id, $db); //2

			//Query quant data
			$quant_data = GetQuantData($projectID, $condition_id, $db);  $total_progress++; UpdateProgress($total_progress, $possible_progress, $process_id, $db); //3
			$background_id = SelectBackground($quant_data, $fcCutoff, $fcSymbol, $pValCutoff, $pValSymbol);  $total_progress+=2; UpdateProgress($total_progress, $possible_progress, $process_id, $db);//4,5
		 	$fisher_go_array = $background_id ===0 ? BuildGOTermArrayMeasBackground($quant_data, $standard_molecule_to_go_terms) : BuildGoTermArrayDBBackground($quant_data, $standard_molecule_to_go_terms, $standard_molecules);  $total_progress+=3; UpdateProgress($total_progress, $possible_progress, $process_id, $db); //6, 7, 8
		 	$p_value_array = GetPValueArray($fisher_go_array);  $total_progress++; UpdateProgress($total_progress, $possible_progress, $process_id, $db); // This is good to be added to the database //9
		 	//add to database is ten
		 	AddGOEnrichmentsToDatabase($projectID, $branch_id, $condition_id, $input_string, $p_value_array, $background_id, $db,$process_id);  $total_progress++; UpdateProgress($total_progress, $possible_progress, $process_id, $db);
	 	}
	 	catch (Exception $e)
		{
			$total_progress = ($curr_cond_count * 10);
		}
	 	
	}
	SetCompleted($process_id, $db);

}

function SetRunning($process_id, $db)
{
	$query = "UPDATE go_enrichment_analysis_queue SET running=1 WHERE process_id=:process_id";
	$query_params = array(':process_id' => $process_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}
function SetCompleted($process_id, $db)
{
	$query = "UPDATE go_enrichment_analysis_queue SET running=0, complete=1 WHERE process_id=:process_id";
	$query_params = array(':process_id' => $process_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}
function UpdateProgress($total_progress, $possible_progress, $process_id, $db)
{
	$curr_percent = (int)(($total_progress/$possible_progress)*100);
	$query = "UPDATE go_enrichment_analysis_queue SET progress=:curr_progress WHERE process_id=:process_id";
	$query_params = array(':curr_progress' => $curr_percent, ':process_id' => $process_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}
function AddGOEnrichmentsToDatabase($projectID, $branch_id, $condition_id, $input_string, $p_value_array, $background_id, $db,$process_id)
{
	//query set id from condition id and project id, 
	$query = "SELECT set_id FROM project_conditions WHERE condition_id=:condition_id";
	$query_params = array(':condition_id' => $condition_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$set_id = $row['set_id'];

	//lock the inputs table
	LockInputsTable($db);

	//add the inputs to go_enrichment_analysis_inputs
	$query = "INSERT INTO go_enrichment_analysis_inputs (project_id, branch_id, set_id, condition_id, input_string, process_id) VALUES (:project_id, :branch_id, :set_id, :condition_id, :input_string, :process_id)";
	$query_params = array(':project_id' => $projectID, ':branch_id' => $branch_id, ':set_id' => $set_id, ':condition_id' => $condition_id, ':input_string' => $input_string, ':process_id' => $process_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);

	//get analysis id from go_inputs table
	$analysis_id = $db->lastInsertID();

	//unlock the inputs table
	UnlockTable($db);


	//add all data to the go_enrichment_analysis_results table
	$insertArray = array();
	foreach ($p_value_array as $entry) {
		array_push($insertArray, array($analysis_id, $entry['go_term_id'], $entry['A'], $entry['B'],  $entry['C'],  $entry['D'],  $entry['p_value'],  $entry['p_value_fdr'],  $entry['p_value_bonferroni'], $background_id, $process_id, $projectID, $branch_id));
	}
	$chunked_array = array_chunk($insertArray, 2000);

			foreach ($chunked_array as $chunk) {
				$row_length = count($chunk[0]);
				$nb_rows = count($chunk);
				$length = $row_length * $nb_rows;
				$args = implode(',', array_map(
					function($el) { return '('.implode(',', $el).')'; },
					array_chunk(array_fill(0, $length, '?'), $row_length)
					));

				$query_params = array();
				foreach($chunk as $array)
				{
					foreach($array as $value)
					{
						$query_params[] = $value;
					}
				}
				$insertText = "INSERT INTO go_enrichment_analysis_results (analysis_id, go_term_id, a, b, c, d, p_value, p_value_fdr, p_value_bonferroni, background, process_id, project_id, branch_id) VALUES " . $args;
				try{
					$stmt = $db->prepare($insertText);
					$result = $stmt->execute($query_params);
				}
				catch (PDOException $ex) {
					//die("Failed to run query: " . $ex->getMessage());
				}
			}
}
function GetConditionsFromBranch($branch_id, $db, $repeat, $start_condition_id, $projectID)
{
	$return_array = array();
	if($repeat==="0" || $repeat===0)
	{
		array_push($return_array, $start_condition_id);
		return $return_array;
	}
	else
	{
		$query = "SELECT condition_id FROM project_conditions WHERE branch_id=:branch_id AND project_id=:project_id AND is_control=0";
		$query_params = array(':branch_id' => $branch_id, ':project_id'=> $projectID);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		foreach ($row as $entry) {
			array_push($return_array, $entry['condition_id']);
		}
		return $return_array;
	}
}
function GetStandardMoleculeGOTermArray($projectID, $condition_id, $db)
{
	//query set id from project conditions

	//query organism id
	$query = "SELECT b.organism_id FROM project_conditions AS a JOIN project_files AS b ON a.set_id=b.set_id WHERE a.condition_id=:condition_id AND a.project_id=:project_id";
	$query_params = array(':condition_id' => $condition_id, ':project_id'=> $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if (!$row)
	{
		//return error message here
	}
	$organism_id = $row['organism_id'];
	$query = "SELECT DISTINCT(a.molecule_id), b.go_term_id FROM standard_molecules AS a JOIN gene_ontology_single_term AS b ON a.molecule_id=b.standard_molecule_id WHERE a.organism_id=:organism_id";
	$query_params = array(':organism_id' => $organism_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	$return_array = array();
	foreach ($row as $entry) {
		$mol_id = $entry['molecule_id'];
		$term_id = $entry['go_term_id'];
		if(!array_key_exists($mol_id, $return_array))
		{
			$return_array[$mol_id] = array();
		}
		array_push($return_array[$mol_id], $term_id);
	}
	return $return_array;
	//query molecules
}
function GetStandardMoleculeIDArray($projectID, $condition_id, $db)
{
	$query = "SELECT b.organism_id FROM project_conditions AS a JOIN project_files AS b ON a.set_id=b.set_id WHERE a.condition_id=:condition_id AND a.project_id=:project_id";
	$query_params = array(':condition_id' => $condition_id, ':project_id'=> $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if (!$row)
	{
		//return error message here
	}
	$organism_id = $row['organism_id'];
	$query = "SELECT DISTINCT(a.molecule_id) FROM standard_molecules AS a WHERE a.organism_id=:organism_id";
	$query_params = array(':organism_id' => $organism_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	$return_array = array();
	foreach ($row as $entry) {
		$mol_id = $entry['molecule_id'];
		if(!array_key_exists($mol_id, $return_array))
		{
			$return_array[$mol_id] = "";
		}
	}
	return $return_array;
}
function GetQuantData($projectID, $condition_id, $db)
{
	$query = "SELECT fold_change_control_norm, p_value_control_norm, standard_molecule_id, unique_identifier_id FROM data_descriptive_statistics WHERE condition_id=:condition_id AND project_id=:project_id";
	$query_params = array(':condition_id' => $condition_id, ':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	$return_array = array();
	foreach ($row as $entry) {
		$mol_id = $entry['standard_molecule_id'];
		$fc = $entry['fold_change_control_norm'];
		$pVal = $entry['p_value_control_norm'];
		$unique_identifier_id = $entry['unique_identifier_id'];
		$return_array[$unique_identifier_id] = array('fc'=> $fc, 'pVal' => $pVal, 'passCutoff' => 0, 'molID' => $mol_id);
	}
	return $return_array;
}
function SelectBackground(&$quant_data, $fcCutoff, $fcSymbol, $pValCutoff, $pValSymbol)
{
	$pass_filter_count = 0;
	$total_count = count($quant_data);
	$threshold = $total_count* (2/3);
	foreach ($quant_data as $key => $value) {
		if ($fcSymbol==="<" && $pValSymbol==="<")
		{
			$value['fc'] < $fcCutoff && $value['pVal'] < $pValCutoff ? $pass_filter_count++ AND $quant_data[$key]['passCutoff'] = 1 : null;
		}
		if ($fcSymbol==="<" && $pValSymbol===">")
		{
			$value['fc'] < $fcCutoff && $value['pVal'] > $pValCutoff ? $pass_filter_count++ AND $quant_data[$key]['passCutoff'] = 1 : null;
		}
		if ($fcSymbol==="<" && $pValSymbol==="> or <")
		{
			$value['fc'] < $fcCutoff ? $pass_filter_count++ AND $quant_data[$key]['passCutoff'] = 1 : null;
		}
		if ($fcSymbol===">" && $pValSymbol==="<")
		{
			$value['fc'] > $fcCutoff && $value['pVal'] < $pValCutoff ? $pass_filter_count++ AND $quant_data[$key]['passCutoff'] = 1 : null;
		}
		if ($fcSymbol===">" && $pValSymbol===">")
		{
			$value['fc'] > $fcCutoff && $value['pVal'] > $pValCutoff ? $pass_filter_count++ AND $quant_data[$key]['passCutoff'] = 1 : null;
		}
		if ($fcSymbol===">" && $pValSymbol==="> or <")
		{
			$value['fc'] > $fcCutoff ? $pass_filter_count++ AND $quant_data[$key]['passCutoff'] = 1 : null;
		}
		if ($fcSymbol==="> or <" && $pValSymbol==="<")
		{
			$value['pVal'] < $pValCutoff ? $pass_filter_count++ AND $quant_data[$key]['passCutoff'] = 1 : null;
		}
		if ($fcSymbol==="> or <" && $pValSymbol===">")
		{
			 $value['pVal'] > $pValCutoff ? $pass_filter_count++ AND $quant_data[$key]['passCutoff'] = 1 : null;
		}
		if ($fcSymbol==="> or <" && $pValSymbol==="> or <")
		{
			$pass_filter_count++;
			$quant_data[$key]['passCutoff'] = 1;
		}
	}
	$return_val = $pass_filter_count > $threshold ? 1 : 0; //1 means use protein db as background, 0 means use measured data
	return $return_val;
}
function BuildGOTermArrayMeasBackground($quant_data, $sm_to_go)
{
	$fisher_go_array = array();
	foreach ($sm_to_go as $key => $value) {
		foreach ($value as $go_id) {
			if(!array_key_exists($go_id, $fisher_go_array))
			{
				$fisher_go_array[$go_id] = array('A' => 0, 'B' => 0, 'C'=> 0, 'D' => 0);
			}
		}
	}
	foreach ($quant_data as $key => $value) {
		//standard molecule id = key
		$mol_go_ids = array();
		$lookup_array = array();
		if (array_key_exists($value['molID'], $sm_to_go))
		{
			$mol_go_ids = $sm_to_go[$value['molID']];
			foreach ($mol_go_ids as $go_id) {
				$lookup_array[$go_id] ="";
			}
		}

		foreach ($fisher_go_array as $go_id => $go_array) {
			if (array_key_exists($go_id, $lookup_array))
			{
				$value['passCutoff']===1 ? $fisher_go_array[$go_id]['A']++ : $fisher_go_array[$go_id]['B']++;
			}
			else
			{
				$value['passCutoff']===1 ? $fisher_go_array[$go_id]['C']++ : $fisher_go_array[$go_id]['D']++;
			}
		}
	}
	$return_array = array_filter($fisher_go_array, function($obj){
		if($obj['A']>0)
		{
			return true;
		}
		return false;
	});
	return $return_array;
}
function BuildGoTermArrayDBBackground($quant_data, $sm_to_go, $standard_molecules)
{
	$fisher_go_array = array();
	foreach ($sm_to_go as $key => $value) {
		foreach ($value as $go_id) {
			if(!array_key_exists($go_id, $fisher_go_array))
			{
				$fisher_go_array[$go_id] = array('A' => 0, 'B' => 0, 'C'=> 0, 'D' => 0);
			}
		}
	}
	foreach ($standard_molecules as $key => $val) {
		//standard molecule id = key
		$mol_go_ids = array();
		$lookup_array = array();
		if (array_key_exists($key, $sm_to_go))
		{
			$mol_go_ids = $sm_to_go[$key];
			foreach ($mol_go_ids as $go_id) {
				$lookup_array[$go_id] ="";
			}
		}

		$value = null; 
		if (array_key_exists($key, $quant_data))
		{
			$value = $quant_data[$key];
		}

		
		foreach ($fisher_go_array as $go_id => $go_array) {
			if (array_key_exists($go_id, $lookup_array)) //this mol has this term
			{
				if ($value!==null)
				{
					$value['passCutoff']===1 ? $fisher_go_array[$go_id]['A']++ : $fisher_go_array[$go_id]['B']++;
				}
				else
				{
					$fisher_go_array[$go_id]['B']++;
				}
			}
			else
			{
				if ($value!==null)
				{
					$value['passCutoff']===1 ? $fisher_go_array[$go_id]['C']++ : $fisher_go_array[$go_id]['D']++;
				}
				else
				{
					$fisher_go_array[$go_id]['D']++;
				}
			}
		}
	}
	$return_array = array_filter($fisher_go_array, function($obj){
		if($obj['A']>0)
		{
			return true;
		}
		return false;
	});
	return $return_array;
}
function GetPValueArray($fisher_go_array)
{
	$p_value_array = array();
	$array_count = count($fisher_go_array);
	foreach ($fisher_go_array as $key => $value) {
		$curr_p_value = GetPValue($value['A'], $value['B'], $value['C'], $value['D']);
		$p_value_array[$key] = array('p_value' => $curr_p_value, 'p_value_bonferroni' => min(1,($curr_p_value*$array_count)), 'p_value_fdr' => 0, 'go_term_id' => $key,
			'A' => $value['A'], 'B' => $value['B'], 'C' => $value['C'], 'D' => $value['D']);
	}
	GetFDRPValues($p_value_array);
	$return_array = array_filter($p_value_array, function($obj){
		if($obj['p_value']<0.05)
		{
			return true;
		}
		return false;
	});

	usort($return_array, "PValueSort");
	return $return_array;
}
function PValueSort($a, $b)
{
	return ($a['p_value'] > $b['p_value']);
}
function GetFDRPValues(&$p_value_array)
{
	$control_p_value_array = array();
	foreach ($p_value_array as $key => $value) {
		array_push($control_p_value_array, $value['p_value']);
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

	foreach ($p_value_array as $key => $value) {
		$lookup = (string)$value['p_value'];
		$fdr_p_value = $control_p_value_mapping_array[$lookup];
		$p_value_array[$key]['p_value_fdr']=$fdr_p_value;
	}
}
function AnalysisPerformed($input_string, $condition_id, $projectID, $db)
{
	$query = "SELECT analysis_id FROM go_enrichment_analysis_inputs WHERE input_string=:input AND project_id=:project_id AND condition_id=:condition_id";
	$query_params = array(':input' => $input_string, ':project_id' => $projectID, ':condition_id' => $condition_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		return false;
	}
	return true;
}
function ParseInputString($input_string)
{
	$array_split = explode("|", $input_string);
	$fcPortion = $array_split[0];
	$pValPortion = $array_split[1];

	$fcParts = explode(":", $fcPortion);
	$pValParts = explode(":", $pValPortion);

	$fcSymbol = "";
	switch ($fcParts[1]) {
		case 'L':
			$fcSymbol = "<";
			break;
		case 'G':
			$fcSymbol = ">";
			break;
		case 'E':
			$fcSymbol="> or <";
			break;
	}
	$fcCutoff = (float)$fcParts[2];

	$pValSymbol = "";
	switch ($pValParts[1]) {
		case 'L':
			$pValSymbol = "<";
			break;
		case 'G':
			$pValSymbol = ">";
			break;
		case 'E':
			$pValSymbol ="> or <";
			break;
	}
	$pValCutoff = (float)$pValParts[2];

	$return_array = array('fcCutoff' => $fcCutoff, 'fcSymbol' => $fcSymbol, 'pValCutoff' => $pValCutoff, 'pValSymbol' => $pValSymbol);

	return $return_array;
}


function LockTable($db)
{
	$lockText = "LOCK TABLES go_enrichment_analysis_queue WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}
function LockInputsTable($db)
{
	$lockText = "LOCK TABLES go_enrichment_analysis_inputs WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}
function UnlockTable($db)
{
	$unlockText = "UNLOCK TABLES";
	$stmt = $db->prepare($unlockText);
	$result = $stmt->execute();
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
