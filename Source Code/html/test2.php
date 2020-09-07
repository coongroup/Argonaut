<?php

/*$mean = 2;
$sd = 1.0;
for ($i=0; $i<50000; $i++)
{
    echo(sample_from_norm_dist($mean, $sd) . "<br>");
}
*/
/*require('config.php');
$projectID='iVM9erh';
$set_id='iVM9erh-1S';

$query = "LOAD DATA LOCAL INFILE '/home/coonnlmj/public_html/DV/" . $projectID . "/" . $set_id . "_data_replicate_data.txt' INTO TABLE data_replicate_data FIELDS TERMINATED BY '\t' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\r\n' (project_id, unique_identifier_id, quant_value, quant_value_log2, quant_value_raw, replicate_id, condition_id, set_id, branch_id, file_id, is_imputed, standard_molecule_id)";
	$stmt = $db->prepare($query, array(PDO::MYSQL_ATTR_LOCAL_INFILE => true));
	$result = $stmt->execute();*/
	
function random_float ($min,$max) {
   return ($min+lcg_value()*(abs($max-$min)));
}

function sample_from_norm_dist ($mean, $sd)
{
    $u1 = 1.0-random_float(0,1);
    $u2 = 1.0-random_float(0,1);
    $randStdNormal = sqrt(-2.0 * log($u1)) * sin(2.0 * pi() * $u2);
    $randNormal = $mean + ($sd * $randStdNormal);
    return $randNormal;
}