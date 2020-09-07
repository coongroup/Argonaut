<?php

require("config.php");

if(empty($_SESSION['user']))
{
   // header("Location: index.html");
    //die("Redirecting to index.html");
}

$term_id = $_POST['ti'];
$query ="SELECT term AS termFullName, external_id AS termExtID, namespace AS termNamespace, definition AS termDef FROM gene_ontology WHERE term_id=:term_id";
$query_params = array(':term_id'=> $term_id);
$stmt = $db->prepare($query);
$result = $stmt->execute($query_params);
$row = $stmt->fetch();
echo(json_encode($row));
