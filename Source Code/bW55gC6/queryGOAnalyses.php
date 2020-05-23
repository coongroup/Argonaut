<?php

require("config.php");

if(empty($_SESSION['user']))
{
   // header("Location: index.html");
    //die("Redirecting to index.html");
}

$condition_id = $_POST['ci'];
//$condition_id = 'HZHocxu-1C';
$query ="SELECT analysis_id, input_string FROM go_enrichment_analysis_inputs WHERE condition_id=:condition_id";
$query_params = array(':condition_id'=> $condition_id);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
foreach ($row as $entry) {
	array_push($data, array('id'=> $entry['analysis_id'], 'display'=> GetDisplayString($entry['input_string'])));
}

echo(json_encode($data));

function GetDisplayString($input_string)
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
	$display = "Fold change " . $fcSymbol . " " . $fcCutoff . " and P-value " . $pValSymbol . " " . $pValCutoff;
	return $display;
}
