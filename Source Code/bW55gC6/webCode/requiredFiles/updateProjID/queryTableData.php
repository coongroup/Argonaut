<?php
header('Content-type: text/plain; charset=utf-8');
require("config.php");

//if (empty($_SESSION['user'])) {
   // header("Location: index.html");
    //die("Redirecting to index.html");
//}


$term = ($_POST['t']);
//$term = "ATG5 KO-A (Galactose–ATG) (Replicate)";
$projectID=-1;
$query = "SELECT * FROM data_query_terms WHERE query_term_text=:term AND project_id=:project_id";
$query_params = array(':project_id' => $projectID, ':term' => $term);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data = array();
$count=0;
if($row)
{
    foreach ($row as $entry) {
        array_push($data, $entry);
        $count++;
    }
}

if (strpos($term, '(Replicate)') && $count>0)
{
    $query = "SELECT data_replicate_data.quant_value, data_unique_identifiers.unique_identifier_text, project_conditions.condition_name, project_sets.set_name, project_branches.branch_name, data_replicate_data.unique_identifier_id AS id FROM data_replicate_data JOIN data_unique_identifiers ON data_unique_identifiers.unique_identifier_id=data_replicate_data.unique_identifier_id"
    . " JOIN project_conditions ON project_conditions.condition_id=data_replicate_data.condition_id JOIN project_sets ON project_sets.set_id=data_replicate_data.set_id JOIN project_branches ON project_branches.branch_id=data_replicate_data.branch_id"
    . " WHERE replicate_id=:replicate_id AND data_replicate_data.use_data=1";
    $query_params = array(':replicate_id' => $data[0]["replicate_id"]);

}


if (strpos($term, '(Condition)')  && $count>0)
{
    $query = "SELECT data_condition_data.avg_quant_value, data_condition_data.all_quant_values, data_condition_data.std_dev_quant_value, data_condition_data.cv_quant_values, data_descriptive_statistics.fold_change_mean_norm, data_descriptive_statistics.fold_change_control_norm,"
    . "data_descriptive_statistics.p_value_mean_norm, data_descriptive_statistics.p_value_control_norm, data_unique_identifiers.unique_identifier_text, project_sets.set_name, project_branches.branch_name, data_descriptive_statistics.fdr_p_value_control_norm, data_descriptive_statistics.bonferroni_p_value_control_norm, data_descriptive_statistics.fdr_p_value_mean_norm, data_descriptive_statistics.bonferroni_p_value_mean_norm, data_descriptive_statistics.unique_identifier_id AS id FROM data_condition_data JOIN data_descriptive_statistics ON (data_descriptive_statistics.condition_id=data_condition_data.condition_id AND data_descriptive_statistics.unique_identifier_id=data_condition_data.unique_identifier_id)"
    . " JOIN data_unique_identifiers ON data_unique_identifiers.unique_identifier_id=data_condition_data.unique_identifier_id JOIN project_branches ON project_branches.branch_id=data_condition_data.branch_id JOIN project_sets ON project_sets.set_id=data_condition_data.set_id WHERE data_condition_data.condition_id=:condition_id AND data_condition_data.use_data=1";
    $query_params = array(':condition_id' => $data[0]["condition_id"]);
}


//if molecule

if (strpos($term, '(Molecule)')  && $count>0)
{
   $query = "SELECT data_condition_data.avg_quant_value, data_condition_data.all_quant_values, data_condition_data.std_dev_quant_value, data_condition_data.cv_quant_values, data_descriptive_statistics.fold_change_mean_norm, data_descriptive_statistics.fold_change_control_norm,"
   . "data_descriptive_statistics.p_value_mean_norm, data_descriptive_statistics.p_value_control_norm, data_unique_identifiers.unique_identifier_text, project_conditions.condition_name, project_sets.set_name, project_branches.branch_name, data_descriptive_statistics.fdr_p_value_control_norm, data_descriptive_statistics.bonferroni_p_value_control_norm, data_descriptive_statistics.fdr_p_value_mean_norm, data_descriptive_statistics.bonferroni_p_value_mean_norm, data_descriptive_statistics.unique_identifier_id AS id FROM data_condition_data JOIN data_descriptive_statistics ON (data_descriptive_statistics.condition_id=data_condition_data.condition_id AND data_descriptive_statistics.unique_identifier_id=data_condition_data.unique_identifier_id)"
   . " JOIN data_unique_identifiers ON data_unique_identifiers.unique_identifier_id=data_condition_data.unique_identifier_id JOIN project_conditions ON project_conditions.condition_id=data_condition_data.condition_id JOIN project_branches ON project_branches.branch_id=data_condition_data.branch_id JOIN project_sets on project_sets.set_id=data_condition_data.set_id WHERE data_condition_data.unique_identifier_id=:unique_identifier_id AND data_condition_data.use_data=1";
   $query_params = array(':unique_identifier_id' => $data[0]["unique_identifier_id"]);
}

$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetchAll();
$data2 = array();
if($row)
{
    foreach ($row as $entry) {
        array_push($data2, $entry);
        $count++;
    }
}

echo(json_encode($data2));

?>
