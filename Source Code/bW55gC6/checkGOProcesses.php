<?php

require("config.php");

if(empty($_SESSION['user']))
{
   // header("Location: index.html");
    //die("Redirecting to index.html");
}
//{fcs: $scope.fcSymbol, fcc: $scope.goFCCutoff, pvs: $scope.pValueSymbol, pvc: $scope.goPValueCutoff, ci: $scope.goCondition.condition_id, r: $scope.repeatAllConds ? 1 : 0};
$projectID='bW55gC6';

$query = "SELECT display, process_id, progress, complete FROM go_enrichment_analysis_queue WHERE project_id=:project_id";
$query_params = array(':project_id'=> $projectID);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();

echo(json_encode($row));
