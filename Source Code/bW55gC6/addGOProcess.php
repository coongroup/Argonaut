<?php

require("config.php");

if(empty($_SESSION['user']))
{
   // header("Location: index.html");
    //die("Redirecting to index.html");
}
//{fcs: $scope.fcSymbol, fcc: $scope.goFCCutoff, pvs: $scope.pValueSymbol, pvc: $scope.goPValueCutoff, ci: $scope.goCondition.condition_id, r: $scope.repeatAllConds ? 1 : 0};
$projectID='bW55gC6';
$start_condition_id = $_POST['ci'];
$fcSymbol = $_POST['fcs'];
$fcCutoff = $_POST['fcc'];
$pValSymbol = $_POST['pvs'];
$pValCutoff = $_POST['pvc'];
$branch_id = $_POST['bi'];
$repeat = $_POST['r'];

//Construct input string
$input_string = ConstructAnalysisString($pValCutoff, $pValSymbol, $fcCutoff, $fcSymbol);
$display_string = "Fold change " . $fcSymbol . " " . $fcCutoff . " and P-value " . $pValSymbol. " " . $pValCutoff;
//Do Checks
if(!CheckSymbols($fcSymbol, $pValSymbol))
{
	$data = array("result"=>false, "message"=>"Invalid symbol input!");
	echo(json_encode($data));
	return;
}
if(!CheckCutoffs($pValCutoff, $fcCutoff))
{
	$data = array("result"=>false, "message"=>"Invalid cutoff inputs!");
	echo(json_encode($data));
	return;
}
if(!DataHasStandardMolecules($start_condition_id, $projectID, $db))
{
	$data = array("result"=>false, "message"=>"Unable to map GO terms to the selected condition!");
	echo(json_encode($data));
	return;
}
if(AnalysisPerformed($input_string, $start_condition_id, $projectID, $db, $display_string))
{
	$data = array("result"=>false, "message"=>"This analysis has already been performed! Please update your selected cutoffs and try again.");
	echo(json_encode($data));
	return;
}

//Add new process to queue
AddNewProcessToQueue($projectID, $branch_id, $start_condition_id, $repeat, $input_string, $fcSymbol, $fcCutoff, $pValSymbol, $pValCutoff, $db);

//Start up a new process if necessary
StartProcessIfNeeded($projectID, $db);

$data = array("result"=>true, "message"=>"GO Enrichments are currently being calculated and you can monitor progress here. Once completed the results from this analysis can be accessed by click the associated option under 'Previous Analyses.'");
	echo(json_encode($data));
	return;

//Preliminary Check Functions
function DataHasStandardMolecules($condition_id, $projectID, $db)
{
	$query = "SELECT standard_molecule_id FROM data_descriptive_statistics WHERE standard_molecule_id!=-1 AND condition_id=:condition_id AND project_id=:project_id LIMIT 1";
	$query_params = array(':condition_id' => $condition_id, ':project_id'=> $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		return false;
	}
	return true;
}
function AnalysisPerformed($input_string, $condition_id, $projectID, $db, $display_string)
{
	$query = "SELECT analysis_id FROM go_enrichment_analysis_inputs WHERE input_string=:input AND project_id=:project_id AND condition_id=:condition_id";
	$query_params = array(':input' => $input_string, ':project_id' => $projectID, ':condition_id' => $condition_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if($row)
	{
		return true;
	}

	$query = "SELECT process_id FROM go_enrichment_analysis_queue WHERE display=:input AND project_id=:project_id AND condition_id=:condition_id";
	$query_params = array(':input' => $display_string, ':project_id' => $projectID, ':condition_id' => $condition_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if($row)
	{
		return true;
	}

	return false;
}
function CheckSymbols($fcSymbol, $pValSymbol)
{
	if ($fcSymbol!=="<" && $fcSymbol!==">" && $fcSymbol!=="> or <")
	{
		return false;
	}
	if ($pValSymbol!=="<" && $pValSymbol!==">" && $pValSymbol!=="> or <")
	{
		return false;
	}
	return true;
}
function CheckCutoffs($pValCutoff, $fcCutoff)
{
	if (!is_numeric($pValCutoff) || !is_numeric($fcCutoff))
	{
		return false;
	}
	$floatP = (float)$pValCutoff;
	if ($floatP > 1 || $floatP < 0)
	{
		return false;
	}
	$floatFC = (float)$fcCutoff;
	if($floatFC > 50 || $floatFC <-50)
	{
		return false;
	}
	return true;
}

//Helper Functions
function ConstructAnalysisString($pValCutoff, $pValSymbol, $fcCutoff, $fcSymbol)
{
	$pValSymbolShort = "";
	$fcSymbolShort = "";
	switch($pValSymbol)
	{
		case "<": $pValSymbolShort = "L"; break;
		case ">": $pValSymbolShort = "G"; break;
		case "> or <": $pValSymbolShort = "E"; break;
	}
	switch($fcSymbol)
	{
		case "<": $fcSymbolShort = "L"; break;
		case ">": $fcSymbolShort = "G"; break;
		case "> or <": $fcSymbolShort = "E"; break;
	}
	$input_string = "FC:" . $fcSymbolShort . ":" . $fcCutoff . "|P:" . $pValSymbolShort . ":" . $pValCutoff;
	return $input_string; 
}

function AddNewProcessToQueue($projectID, $branch_id, $condition_id, $repeat, $input_string, $fcSymbol, $fcCutoff, $pValSymbol, $pValCutoff, $db)
{
	$display_string = "Fold change " . $fcSymbol . " " . $fcCutoff . " and P-value " . $pValSymbol. " " . $pValCutoff;

	$query = "INSERT INTO go_enrichment_analysis_queue (project_id, branch_id, condition_id, repeat_process, display, input_string, task_creation_time) VALUES (:project_id, :branch_id, :condition_id, :repeat, :display, :input_string, :time)";
	$query_params = array(':project_id' => $projectID, ':branch_id' => $branch_id, ':condition_id' => $condition_id, ':repeat' => $repeat, ':display' => $display_string, ':input_string' => $input_string, ':time' => date("Y-m-d H:i:s"));
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
}
function StartProcessIfNeeded($projectID, $db)
{
	LockTable($db);
	$query = "SELECT process_id FROM go_enrichment_analysis_queue WHERE project_id=:project_id AND running=1 LIMIT 1";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	if(!$row)
	{
		$ch = curl_init();
 
		//curl_setopt($ch, CURLOPT_URL, "https://coonlabdatadev.com/DV/" . $projectID . "/calculateGOEnrichments.php");
		curl_setopt($ch, CURLOPT_URL, "127.0.0.1/bW55gC6/calculateGOEnrichments.php");
		//curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		 
		curl_exec($ch);
		curl_close($ch);
	}
	UnlockTable($db);
}

function LockTable($db)
{
	$lockText = "LOCK TABLES go_enrichment_analysis_queue WRITE";
	$stmt = $db->prepare($lockText);
	$result = $stmt->execute();
}
function UnlockTable($db)
{
	$unlockText = "UNLOCK TABLES";
	$stmt = $db->prepare($unlockText);
	$result = $stmt->execute();
}
