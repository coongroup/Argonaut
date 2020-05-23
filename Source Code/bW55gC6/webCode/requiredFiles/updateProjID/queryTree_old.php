<?php

require("config.php");

if(empty($_SESSION['user']))

{

    header("Location: index.html");

    die("Redirecting to index.html");

}


$server = mysql_connect($host, $username, $password);

mysql_select_db($dbname);


//complete tree or display tree (you can probably just return both in a single array?)

//for right now just worry about display tree.


//project id

$projectID=-1;

    //($_POST['pi']);


//result of the following will tell you whether you need to get two separate trees

$argLine1 = "SELECT COUNT(*) FROM project_branches WHERE project_id='" . $projectID . "'";

$query1 = mysql_query($argLine1);


if ( ! $query1 ) {

    echo mysql_error();

    die;

}


//ignore the branch for right now go straight to sets, conditions, replicates


$list = array();


$argLine2 = "SELECT * FROM projects WHERE project_id='" . $projectID . "'";

$query2 = mysql_query($argLine2);

if ($query2 !== FALSE) {

    while ($rows = mysql_fetch_array($query2)) { //this is how you do it

        if (!is_null($rows[0])) {

            $list[]=array('name'=>$rows["project_id"], 'value'=>$rows["project_name"], 'parent'=>"null", 'x0'=>0, 'y0'=>0);

        }

    }

}


$argLine6 = "SELECT project_branches.*, projects.project_id FROM project_branches JOIN projects ON projects.project_id=project_branches.project_id WHERE project_branches.project_id='" . $projectID . "'";

$query6 = mysql_query($argLine6);

if ($query6 !== FALSE) {

    while ($rows = mysql_fetch_array($query6)) { //this is how you do it

        if (!is_null($rows[0])) {

            $list[]=array('name'=>$rows["branch_id"], 'value'=>$rows["branch_name"], 'parent'=>$rows["project_id"], 'x0'=>0, 'y0'=>0);

        }

    }

}


$argLine3 = "SELECT project_sets.*, project_branches.project_id FROM project_sets JOIN project_branches ON project_branches.branch_id=project_sets.branch_id WHERE project_sets.project_id='" . $projectID . "'";

$query3 = mysql_query($argLine3);

if ($query3 !== FALSE) {

    while ($rows = mysql_fetch_array($query3)) { //this is how you do it

        if (!is_null($rows[0])) {

            $list[]=array('name'=>$rows["set_id"], 'value'=>$rows["set_name"], 'parent'=>$rows["branch_id"], 'x0'=>0, 'y0'=>0);

        }

    }

}


$argLine4 = "SELECT project_conditions.*, project_sets.set_id FROM project_conditions JOIN project_sets ON project_sets.set_id=project_conditions.set_id WHERE project_conditions.project_id='" . $projectID . "'";

$query4 = mysql_query($argLine4);

if ($query4 !== FALSE) {

    while ($rows = mysql_fetch_array($query4)) { //this is how you do it

        if (!is_null($rows[0])) {

            $list[]=array('name'=>$rows["condition_id"], 'value'=>$rows["condition_name"], 'parent'=>$rows["set_id"], 'x0'=>0, 'y0'=>0);

        }

    }

}


$argLine5 = "SELECT project_replicates.*, project_conditions.condition_id FROM project_replicates JOIN project_conditions ON project_conditions.condition_id=project_replicates.condition_id WHERE project_replicates.project_id='" . $projectID . "'";

$query5 = mysql_query($argLine5);

if ($query5 !== FALSE) {

    while ($rows = mysql_fetch_array($query5)) { //this is how you do it

        if (!is_null($rows[0])) {

            $list[]=array('name'=>$rows["replicate_id"], 'value'=>$rows["replicate_name"], 'parent'=>$rows["condition_id"], 'x0'=>0, 'y0'=>0);

        }

    }

}


echo(json_encode($list));

?>
