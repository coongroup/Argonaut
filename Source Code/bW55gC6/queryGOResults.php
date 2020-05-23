<?php

require("config.php");

if(empty($_SESSION['user']))
{
   // header("Location: index.html");
    //die("Redirecting to index.html");
}

$analysis_id = $_POST['ai'];
$query = "SELECT a.p_value, a.p_value_fdr, a.p_value_bonferroni, b.term, a.go_term_id, b.external_id, a.a, a.b, a.c, a.d FROM go_enrichment_analysis_results AS a JOIN gene_ontology AS b on a.go_term_id=b.term_id JOIN go_enrichment_analysis_inputs AS c ON a.analysis_id=c.analysis_id WHERE a.analysis_id=:analysis_id AND c.project_id=:project_id";
$query_params = array(':project_id' => $projectID, ':analysis_id'=> $analysis_id);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
echo(json_encode($row));