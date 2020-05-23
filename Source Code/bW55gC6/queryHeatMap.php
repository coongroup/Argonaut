<?php

require('config.php');
require('heatmapcolors.php');

$colorGradient = $_POST['cg']; //normally get this via POST
$minFC = $_POST['min'];
$maxFC = $_POST['max'];
$customGrad = $_POST['cust'];
$minColor = $_POST['minC'];
$midColor = $_POST['midC'];
$maxColor = $_POST['maxC'];
$desiredClusterCount = $_POST['cc'];
$rowDistance = $_POST['rd'];
$rowLinkage = $_POST['rl'];
$colDistance = $_POST['cd'];
$colLinkage = $_POST['cl'];
$useConds = filter_var($_POST['uc'], FILTER_VALIDATE_BOOLEAN);
$branchID = $_POST['bi'];
$excludedNodes = json_decode($_POST['ex'], true);


$outData = array('hasData'=>true,'message'=>"", 'branch'=>$branchID, 'columnNameOrderArray'=>array(), 'rowNameOrderArray'=>array(), 'dataArray'=>array(), 'b64'=>"", 'cb'=>"", 'numClusters' => $desiredClusterCount, 'clusterRanges'=>array(), 'rowClusters'=> "", 'columnClusters'=>"", 'analysis_id'=>"", 'clusterIDs'=>array(), 'figLegend'=>"");

//add some input validation here

//validate non color inputs
if (!ValidateNonColorInputs($minFC, $maxFC, $customGrad, $desiredClusterCount, $rowLinkage, $rowDistance, $colLinkage, $colDistance, $useConds, $branchID, $excludedNodes, $db, $projectID))
{
	$outData['hasData']=false;
	echo(json_encode($outData));
	exit();
}
//Validate color inputs -- for custom gradients set any invalid colors to black
ValidateColorInputs();


$excludedNodeString = "";
foreach ($excludedNodes as $entry) {
	$excludedNodeString .= $entry . '_';
}

$condString = $useConds ? "1" : "0";

//build a string representation of this analysis
	//the last 7 variables are the only ones that matter.
$stringRep = $rowDistance . '|' . $rowLinkage . '|' . $colDistance . '|' . $colLinkage . '|' . $condString . '|' . $branchID . '|' . substr($excludedNodeString,0,strlen($excludedNodeString)-1);;

//check if it already exists in the database.

$query = "SELECT analysis_id FROM hierarchical_clustering_inputs WHERE string_representation=:stringRep AND project_id=:project_id AND branch_id=:branch_id";
$query_params = array(':stringRep' => $stringRep, ':project_id' => $projectID, ':branch_id' => $branchID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();

if(!$row)
{
	//if no then add and trigger the analysis.
	$outData['hasData']=false;
	$outData['message']="You hierarchical clustering analysis has started and you can monitor its progress above. Once the analysis completes it will appear in the 'Previous Heat Maps/Analyses' dropdown where you can select and view the associated heat map and clusters.";
	//$outData['message']=$stringRep;
	/*echo(json_encode($outData));
	exit();*/
	InsertAnalysisInputs($projectID, $branchID, $stringRep, $useConds, $excludedNodes, $rowLinkage, $rowDistance, $colLinkage, $colDistance, $db);


	$query = "SELECT * FROM hierarchical_clustering_inputs WHERE project_id=:project_id AND running=1";
	$query_params = array(':project_id'=>$projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();

	if(!$row)
	{

		$ch = curl_init();
	 
		//curl_setopt($ch, CURLOPT_URL, "https://coonlabdatadev.com/DV/" . $projectID . "/generateHeatMap.php");
		curl_setopt($ch, CURLOPT_URL, "127.0.0.1/bW55gC6/generateHeatMap.php");
		//curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 1);
		 
		curl_exec($ch);
		curl_close($ch);
	}

}
else
{
	$currAnalysisID = $row['analysis_id'];
	$outData['analysis_id'] = $currAnalysisID;

	//check that the processing has completed
	$query = "SELECT COUNT(*) FROM hierarchical_clustering_results WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id' => $row['analysis_id']);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();

	// echo(json_encode($row));
	// exit();

	if ($row['COUNT(*)']==="1")
	{
		//data is here and ready to go
		$outData['hasData']=true;
		$tmpRowClusters = array();
		$threshold = 0;
		$rootNodes = array();
		GetHeatMap($currAnalysisID);
	}
	else
	{
		//processing is still going but hasn't yet finished. return a message to the user that everything is still going.
		$outData['hasData']=false;
		$outData['message']="The specified analysis is currently running and should done shortly. Once the analysis completes it will appear in the 'Previous Heat Maps/Analyses' dropdown where you can select and view the associated heat map and clusters.";
	}
}

echo(json_encode($outData));

function GetHeatMap($analysis_id)
{
	global $branchID;
	global $useConds;
	global $outData;
	global $db;
	global $projectID;
	global $tmpRowClusters;
	global $desiredClusterCount;
	global $threshold;
	global $rootNodes;
	global $customGrad;
	global $minColor;
	global $midColor;
	global $maxColor;
	global $colorGradient;
	global $minFC;
	global $maxFC;
	global $heatmapcolors;

	//need to return rowClusters, columnClusters, rowNameOrderArray, colNameOrderArray, b64 (base64 image), cb (color bar data), clusterRanges

	//prepare your color array

	$colorArray = array();

	switch($customGrad)
	{
		case 1: $colorArray = $heatmapcolors[$colorGradient]; break;
		case 2: $colorArray = create2ColorArray($minColor, $maxColor); break;
		case 3: $colorArray = create3ColorArray($minColor, $midColor, $maxColor); break;
	}

	$range = $maxFC - $minFC;

	//query row clusters and column clusters
	//populate rowOrderArray and nameOrderArray

	$query = "SELECT row_clusters, column_clusters FROM hierarchical_clustering_results WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id' => $analysis_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();

	$rowClusters = json_decode($row['row_clusters'], true);
	$colClusters = json_decode($row['column_clusters'], true);

	//$outData['rowClusters']=$row['row_clusters'];
	//$outData['columnClusters']=$row['column_clusters'];

	$rowOrderArray = array();
	$columnOrderArray = array();

	foreach ($rowClusters as $entry) {
		if($entry['MID']!=="")
		{
			$yVal = $entry['y']-1;
			$rowOrderArray[$entry['MID']]=$yVal;
		}
		$outData['rowClusters'][$entry['CID']] = $entry;
	}

	foreach ($colClusters as $entry) {
		if($entry['MID']!=="")
		{
			$xVal = $entry['x']-1;
			$columnOrderArray[$entry['MID']]=$xVal;
		}
		$outData['columnClusters'][$entry['CID']] = $entry;
	}

	if($useConds)
	{
		$query = "SELECT condition_id, condition_name FROM project_conditions WHERE branch_id=:branch_id";
		$query_params = array(':branch_id' => $branchID);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();

		foreach ($row as $entry) {
			$name = $entry['condition_name'];
			$id = $entry['condition_id'];
			if (array_key_exists($id, $columnOrderArray))
			{
				$index = $columnOrderArray[$id];
				$outData['columnNameOrderArray'][$index] = $name;
			}
		}
	}
	else
	{
		$query = "SELECT replicate_id, replicate_name FROM project_replicates WHERE branch_id=:branch_id";
		$query_params = array(':branch_id' => $branchID);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();

		foreach ($row as $entry) {
			$name = $entry['replicate_name'];
			$id = $entry['replicate_id'];
			if (array_key_exists($id, $columnOrderArray))
			{
				$index = $columnOrderArray[$id];
				$outData['columnNameOrderArray'][$index] = $name;
			}
		}
	}

	$query = "SELECT unique_identifier_id, unique_identifier_text FROM data_unique_identifiers WHERE project_id=:project_id";
	$query_params = array(':project_id' => $projectID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	foreach ($row as $entry) {
		$text = $entry['unique_identifier_text'];
		$id = $entry['unique_identifier_id'];
		if (array_key_exists($id, $rowOrderArray))
		{
			$index = $rowOrderArray[$id];
			$index = count($rowOrderArray) - $index-1;
			$outData['rowNameOrderArray'][$index] = $text;
		}
	}

	$query = "SELECT use_conditions, row_linkage, row_distance, column_linkage, column_distance, control_normalized FROM hierarchical_clustering_inputs WHERE analysis_id=:analysis_id";
	$query_params = array(':analysis_id'=>$analysis_id);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetch();
	$figureLegend = AssembleFigureLegend($row['row_linkage'], $row['row_distance'], $row['column_linkage'], $row['column_distance'], $row['control_normalized'], $row['use_conditions'], count($rowOrderArray),count($columnOrderArray), $desiredClusterCount);
	$outData['figLegend'] = $figureLegend;


	//all of your original heat map data should be written to a file which you can read quickly.
	$original_heat_map_array = json_decode(file_get_contents('heatMap_' . $analysis_id . ".txt"), true);
	
	$tmpRowClusters = array();
	usort($rowClusters, "clusterSort");
	$rowClusterCount = count($rowClusters);
	$threshold = $rowClusters[$desiredClusterCount-1]['d'];

	foreach ($rowClusters as $entry) {
		$tmpRowClusters[$entry['CID']] = $entry;
	}

	$rootNodes = array();
	$startNodeKey = $rowClusters[0]['CID'];

	TraverseTree($tmpRowClusters[$startNodeKey]);

	foreach ($rootNodes as $entry) {
		$rangeArray = array();
		AssembleRanges($tmpRowClusters[$entry], $rangeArray);
		array_push($outData['clusterRanges'], min($rangeArray) . "_" . max($rangeArray));
	}

	usort($outData['clusterRanges'], "clusterRangeSort");

	$tmpClustIndex = 1;
	foreach ($outData['clusterRanges'] as $entry) {
		$outData['clusterIDs'][$entry]=$tmpClustIndex;
		$tmpClustIndex++;
	}

	$dataArray = array();
	$im = imagecreatetruecolor(count($columnOrderArray),count($rowOrderArray));
	$img = imagecreatetruecolor(count($columnOrderArray),count($rowOrderArray));
	$total = 0;

	foreach ($original_heat_map_array as $key => $value) {
		foreach ($value as $key2 => $fcVal) {
			//echo($key2 . " " . $key . "<br>");
			//FlQ2aoJ-1 FlQ2aoJ-3C
			$molID = $key2;
			$condID = $key;
			$fc = $fcVal;
			$adjFC = 0;
			if ($fc < $maxFC && $fc >$minFC)
			{
				$adjFC = ($fc-$minFC)/$range;
				$adjFC = round($adjFC,3);
			}
			if ($fc >= $maxFC)
			{
				$adjFC = 1;
			}
			$columnKey = $columnOrderArray[$condID];
			$rowKey = $rowOrderArray[$molID];

			$rowKey = count($rowOrderArray)-$rowKey-1;

			$fullKey = $columnKey . '_' . $rowKey;

			//$fullKey = $columnKey . '_' .$rowOrderArray[$molID];

			$outData['dataArray'][$fullKey] = round($fc,6);
			$hex = $colorArray[(string)$adjFC];
			$color = hexColorAllocate($im, $hex);
			imagesetpixel($img, $columnKey, $rowKey, $color);

			$total++;
		}
	}

	$colorBar = imagecreatetruecolor(count($colorArray), 1);
	$colorBarImg = imagecreatetruecolor(count($colorArray), 1);
	$colorBarIndex = 0;
	foreach ($colorArray as $key => $value) {
		$hex = hexColorAllocate($colorBar, $value);
		imagesetpixel($colorBarImg, $colorBarIndex, 0, $hex);
		$colorBarIndex++;
	}

	imagepng($img, 'currheatmapimage.png');
	$imagedata = file_get_contents('currheatmapimage.png');
	$outData['b64'] = "data:image/png;base64," . base64_encode($imagedata);

	imagepng($colorBarImg, 'currcolorbar.png');

	$colorBarData = file_get_contents('currcolorbar.png');

	$outData['cb'] = "data:image/png;base64," . base64_encode($colorBarData);
}

function AssembleFigureLegend($rowLink, $rowDist, $colLink, $colDistance, $control_normalized, $use_conditions, $rowCount, $colCount, $clusterCount)
{
	$linkages = array();
	$linkages[1]='average';
	$linkages[2]='complete';
	$linkages[3]='single';

	$distances = array();
	$distances[1]='euclidean';
	$distances[2]='canberra';
	$distances[3]='cosine';
	$distances[4]='manhattan';
	$distances[5]='maximum';
	$distances[6]='pearson';
	$distances[7]='spearman';

	$returnString = "<b>Figure Legend: </b>Heat map displaying hierarchical clustering of ";
	$control_normalized==="1" || $control_normalized===1 ? $returnString .= "control-normalized " : "mean-normalized ";
	$returnString .= "log<sub>2</sub> fold changes of " . $rowCount . " molecules quantified across " . $colCount;
	$use_conditions==="1" || $use_conditions===1 ? $returnString .= " conditions. " : $returnString .= " replicates. ";
	$returnString .= "Row-wise clustering was performed using " . $distances[$rowDist] . " distance and " . $linkages[$rowLink] . " linkage calculations. ";
	$returnString .= "Column-wise clustering was performed using " . $distances[$colDistance] . " distance and " . $linkages[$colLink] . " linkage calculations. ";
	$returnString .= $clusterCount . " distinct hierarchical clusters are represented in the color bar shown alongside the heat map.";

	return $returnString;
 }

function ValidateColorInputs()
{
	global $customGrad;
	global $minColor;
	global $midColor;
	global $maxColor;
	global $colorGradient;

	if ($customGrad===1)
	{
		if (!array_key_exists($colorGradient, $heatmapcolors))
		{
			return false;
		}
		return true;
	}
	if($customGrad===2)
	{
		preg_match('/(#[a-f0-9]{3}([a-f0-9]{3})?)/i', $minColor, $matches);
		if (count($matches)!==3)
		{
			$minColor = "#00000";
		}
		preg_match('/(#[a-f0-9]{3}([a-f0-9]{3})?)/i', $maxColor, $matches);
		if (count($matches)!==3)
		{
			$maxColor = "#00000";
		}
		
		return true;
	}
	if($customGrad===3)
	{
		preg_match('/(#[a-f0-9]{3}([a-f0-9]{3})?)/i', $minColor, $matches);
		if (count($matches)!==3)
		{
			$minColor = "#00000";
		}
		preg_match('/(#[a-f0-9]{3}([a-f0-9]{3})?)/i', $midColor, $matches);
		if (count($matches)!==3)
		{
			$midColor = "#00000";
		}
		preg_match('/(#[a-f0-9]{3}([a-f0-9]{3})?)/i', $maxColor, $matches);
		if (count($matches)!==3)
		{
			$maxColor = "#00000";
		}
		
		return true;
	}
}

function ValidateNonColorInputs($minFC, $maxFC, $customGrad, $desiredClusterCount, $rowLinkage, $rowDistance, $colLinkage, $colDistance, $useConds, $branchID, $excludedNodes, $db, $project_id)
{
	global $outData;

	if (!is_numeric($minFC) || !is_numeric($maxFC) || !is_numeric($customGrad) || !is_numeric($desiredClusterCount) || !is_numeric($rowLinkage) || !is_numeric($rowDistance) || 
		!is_numeric($colLinkage) || !is_numeric($colDistance))
	{
		$outData['message']="Received non-numeric inputs. Please check your inputs and resubmit.";
		return false;
	}
	if ($minFC >= $maxFC)
	{
		$outData['message']="Minimum fold change was greater than maximum fold change. Please change your inputs and resubmit";
		return false;
	}
	if($customGrad>3 || $customGrad <1)
	{
		$outData['message']="Invalid color gradient specified. Please change your inputs and resubmit";
		return false;
	}
	if ($rowLinkage > 3 || $rowLinkage <1)
	{
		$outData['message']="Invalid row linkage specified. Please change your inputs and resubmit";
		return false;
	}
	if ($colLinkage > 3 || $colLinkage <1)
	{
		$outData['message']="Invalid column linkage specified. Please change your inputs and resubmit";
		return false;
	}
	if ($rowDistance > 7 || $rowDistance <1)
	{
		$outData['message']="Invalid row distance specified. Please change your inputs and resubmit";
		return false;
	}
	if ($colDistance > 7 || $colDistance <1)
	{
		$outData['message']="Invalid column distance specified. Please change your inputs and resubmit";
		return false;
	}
	if (!is_bool($useConds))
	{
		$outData['message']="Please change your inputs and resubmit.";
		return false;
	}

	$query = "SELECT branch_id FROM project_branches WHERE project_id=:project_id AND branch_id=:branch_id";
	$query_params = array(':project_id' => $project_id, ':branch_id'=> $branchID);
	$stmt = $db->prepare($query);
	$result = $stmt->execute($query_params);
	$row = $stmt->fetchAll();

	if (count($row)!==1)
	{
		return false;
	}

	if ($useConds)
	{
		$query = "SELECT condition_id FROM project_conditions WHERE project_id=:project_id AND branch_id=:branch_id";
		$query_params = array(':project_id' => $project_id, ':branch_id'=> $branchID);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		$tmpArray = array();
		foreach ($row as $entry) {
			$tmpArray[$entry['condition_id']] = "";
		}

		foreach ($excludedNodes as $node) {
			if (!array_key_exists($node, $tmpArray))
			{
				$outData['message']="Unable to locate all excluded conditions on the server. Please change your inputs and resubmit.";
				return false;
			}
		}
	}
	else
	{
		$query = "SELECT replicate_id FROM project_replicates WHERE project_id=:project_id AND branch_id=:branch_id";
		$query_params = array(':project_id' => $project_id, ':branch_id'=> $branchID);
		$stmt = $db->prepare($query);
		$result = $stmt->execute($query_params);
		$row = $stmt->fetchAll();
		$tmpArray = array();
		foreach ($row as $entry) {
			$tmpArray[$entry['replicate_id']] = "";
		}
		foreach ($excludedNodes as $node) {
			if (!array_key_exists($node, $tmpArray))
			{
				$outData['message']="Unable to locate all excluded replicates on the server. Please change your inputs and resubmit.";
				return false;
			}
		}
	}

	return true;
}

function InsertAnalysisInputs($project_id, $branch_id, $stringRep, $useConds, $excludedNodes, $rowLinkage, $rowDistance, $colLinkage, $colDistance, $db)
{
	$query = "INSERT INTO hierarchical_clustering_inputs (project_id, branch_id, string_representation, use_conditions, excluded_nodes, row_linkage, row_distance, column_linkage, column_distance, task_creation_time) "
	. "VALUES (:project_id, :branch_id, :string_representation, :use_conditions, :excluded_nodes, :row_linkage, :row_distance, :column_linkage, :column_distance, :task_creation_time)";
	$query_params = array(':project_id' => $project_id, ':branch_id' => $branch_id, ':string_representation' => $stringRep,
	 ':use_conditions' => $useConds ? 1 : 0, ':excluded_nodes' => json_encode($excludedNodes), ':row_linkage' => $rowLinkage,
	 ':row_distance' => $rowDistance, ':column_linkage'=> $colLinkage, ':column_distance'=> $colDistance, ':task_creation_time' => date("Y-m-d H:i:s"));
	$stmt = $db->prepare($query); 
            $result = $stmt->execute($query_params); 

}

//Heat map generation helper methods
function hexColorAllocate($im, $hex){
    $hex = ltrim($hex,'#');
    $a = hexdec(substr($hex,0,2));
    $b = hexdec(substr($hex,2,2));
    $c = hexdec(substr($hex,4,2));
    return imagecolorallocate($im, $a, $b, $c); 
}

function hexColorAllocate2($hex){
    $hex = ltrim($hex,'#');
    $a = hexdec(substr($hex,0,2));
    $b = hexdec(substr($hex,2,2));
    $c = hexdec(substr($hex,4,2));
    $retArray = array($a, $b, $c);
    return $retArray;
    //return imagecolorallocate($im, $a, $b, $c); 
}

function create2ColorArray($minColor, $maxColor)
{
	$minRGB = hexColorAllocate2($minColor);
	$maxRGB = hexColorAllocate2($maxColor);

	$colorArray = array();
	$colorArray[0] = $minColor;

	$rdiff = ($maxRGB[0] - $minRGB[0])/1000;
	$gdiff = ($maxRGB[1] - $minRGB[1])/1000;
	$bdiff = ($maxRGB[2] - $minRGB[2])/1000;

	for ($i = 1; $i <= 1000; $i++) {
		$currR = (int)($minRGB[0] + ($i * $rdiff)); $currR = max($currR, 0); $currR = min($currR, 255);
		$currG = (int)($minRGB[1] + ($i * $gdiff)); $currG = max($currG, 0); $currG = min($currG, 255);
		$currB = (int)($minRGB[2] + ($i * $bdiff)); $currB = max($currB, 0); $currB = min($currB, 255);
		$currIndex = (double)($i/1000);
		$colorArray[(string)$currIndex] = sprintf("#%02x%02x%02x", $currR, $currG, $currB);
	}
	$colorArray["1"] = $maxColor;
	return $colorArray;
}

function create3ColorArray($minColor, $midColor, $maxColor)
{
	$minRGB = hexColorAllocate2($minColor);
	$midRGB = hexColorAllocate2($midColor);
	$maxRGB = hexColorAllocate2($maxColor);

	$colorArray = array();
	$colorArray["0"] = $minColor;

	$rdiff = ($midRGB[0] - $minRGB[0])/500;
	$gdiff = ($midRGB[1] - $minRGB[1])/500;
	$bdiff = ($midRGB[2] - $minRGB[2])/500;

	for ($i = 1; $i <= 500; $i++) {
		$currR = (int)($minRGB[0] + ($i * $rdiff)); $currR = max($currR, 0); $currR = min($currR, 255);
		$currG = (int)($minRGB[1] + ($i * $gdiff)); $currG = max($currG, 0); $currG = min($currG, 255);
		$currB = (int)($minRGB[2] + ($i * $bdiff)); $currB = max($currB, 0); $currB = min($currB, 255);
		$currIndex = (double)($i/1000);
		$colorArray[(string)$currIndex] = sprintf("#%02x%02x%02x", $currR, $currG, $currB);
	}

	$colorArray["0.5"] = $midColor;

	$rdiff = ($maxRGB[0] - $midRGB[0])/500;
	$gdiff = ($maxRGB[1] - $midRGB[1])/500;
	$bdiff = ($maxRGB[2] - $midRGB[2])/500;

	for ($i = 1; $i <= 500; $i++) {
		$currR = (int)($midRGB[0] + ($i * $rdiff)); $currR = max($currR, 0); $currR = min($currR, 255);
		$currG = (int)($midRGB[1] + ($i * $gdiff)); $currG = max($currG, 0); $currG = min($currG, 255);
		$currB = (int)($midRGB[2] + ($i * $bdiff)); $currB = max($currB, 0); $currB = min($currB, 255);
		$currIndex = (double)(($i/1000) + 0.500);
		$colorArray[(string)$currIndex] = sprintf("#%02x%02x%02x", $currR, $currG, $currB);
	}

	$colorArray["1"] = $maxColor;
	return $colorArray;
}

function clusterSort($a, $b)
{
	if ($a['d']===$b['d'])
	{
		return 0;
	}
	return $a['d'] > $b['d'] ? -1 : 1;
}

function clusterRangeSort($a, $b)
{
	$aArray = explode("_", $a);
	$bArray = explode("_", $b);

	$aStart = (int)$aArray[0];
	$bStart = (int)$bArray[0];

	return $aStart < $bStart ? -1 : 1; 
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

function AssembleRanges($node, &$array)
{
	global $tmpRowClusters;
	if ($node['MID']==="")
	{
		AssembleRanges($tmpRowClusters[$node['C'][0]], $array);
		AssembleRanges($tmpRowClusters[$node['C'][1]], $array);
	}
	else
	{
		array_push($array, $node['y']);
		return;
	}
}
