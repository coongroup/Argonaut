<?php
    // These variables define the connection information for your MySQL database
	// I'm including the connection credentials here for accessing the mysql database.
	// If you have control over the docker machine. I'd recommend forking the Docker image and create your own mysql user account using root permissions
	// then update these connection strings.
    $username = "docker_user";
    $password = "2ZLy.evYa[qu=$-6";
    $host = "localhost"; 
    $dbname = "docker_db";
    $projectID = 'bW55gC6';

    // this path was hardcoded into all the sqlQueries in AdvancedProcess.php
    // /home/coonnlmj/public_html/DV/" . $projectID . "/" . $set_id . "_the_file_name.txt
    $projectPath = "/var/www/html/" . $projectID . "/";
    
    $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8', PDO::MYSQL_ATTR_LOCAL_INFILE => true);
    try { $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options); }
    catch(PDOException $ex){ die("Failed to connect to the database: " . $ex->getMessage());}
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    header('Content-Type: text/html; charset=utf-8');
	
	// NOTE, I've stripped the actual user account info from this particular config file
	// If you really care, exec into the Docker image and check it out there.
	// Setting the below variables will force automatic login to a specific user account.
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
        $_SESSION['user'] = -1;
        $_SESSION['username']='user';
        $_SESSION['pref']='Docker';
        $_SESSION['last']='User';
        $_SESSION['privleged_user']=1;
        
    }
?>
