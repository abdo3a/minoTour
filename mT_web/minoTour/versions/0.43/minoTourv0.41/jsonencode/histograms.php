<?php

header('Content-Type: application/json');
// checking for minimum PHP version
if (version_compare(PHP_VERSION, '5.3.7', '<')) {
    exit("Sorry, Simple PHP Login does not run on a PHP version smaller than 5.3.7 !");
} else if (version_compare(PHP_VERSION, '5.5.0', '<')) {
    // if you are using PHP 5.3 or PHP 5.4 you have to include the password_api_compatibility_library.php
    // (this library adds the PHP 5.5 password hashing functions to older versions of PHP)
    require_once("../libraries/password_compatibility_library.php");
}

// include the configs / constants for the database connection
require_once("../config/db.php");

// load the login class
require_once("../classes/Login.php");

// load the functions
require_once("../includes/functions.php");

// create a login object. when this object is created, it will do all login/logout stuff automatically
// so this single line handles the entire login process. in consequence, you can simply ...
$login = new Login();

// ... ask if we are logged in here:
if ($login->isUserLoggedIn() == true) {
    // the user is logged in. you can do whatever you want here.
    // for demonstration purposes, we simply show the "you are logged in" view.
    //include("views/index_old.php");*/
	if($_GET["prev"] == 1){
		$mindb_connection = new mysqli(DB_HOST,DB_USER,DB_PASS,$_SESSION['focusrun']);
	}else{
		$mindb_connection = new mysqli(DB_HOST,DB_USER,DB_PASS,$_SESSION['active_run_name']);
	}

	//echo cleanname($_SESSION['active_run_name']);;

	//echo '<br>';

	if (!$mindb_connection->connect_errno) {
		//Check if entry already exists in jsonstore table:
		$jsonjobname="histogram";
			
		$checkrow = "select name,json from jsonstore where name = '" . $jsonjobname . "' ;";
		$checking=$mindb_connection->query($checkrow);
		if ($checking->num_rows ==1){
			//echo "We have already run this!";
			foreach ($checking as $row){
				$jsonstring = $row['json'];
			}
		} else {
			
			$sql_query = "SELECT ROUND(length(basecalled_template.sequence), -2.5) as bucket, COUNT(basecalled_template.sequence) as tempCOUNT,  COUNT(basecalled_complement.sequence) as compCOUNT,   COUNT(basecalled_2d.sequence) as seq2dCOUNT from basecalled_template left join basecalled_complement using (basename_id) left join basecalled_2d using (basename_id) group by bucket;";

			$sql_execute=$mindb_connection->query($sql_query);		
		
		$category = array();
		$category['name'] = 'Size';

		$series1 = array();
		$series1['name'] = 'Template';

		$series2 = array();
		$series2['name'] = 'Complement';

		$series3 = array();
		$series3['name'] = '2d';
		
		if ($sql_execute->num_rows >=1) {
			foreach ($sql_execute as $row){
				$category['data'][]= $row['bucket'];
			    $series1['data'][] = $row['tempCOUNT'];
			    $series2['data'][] = $row['compCOUNT'];
			    $series3['data'][] = $row['seq2dCOUNT'];   
			}
		}

		$result = array();
		array_push($result,$category);
		array_push($result,$series1);
		array_push($result,$series2);
		array_push($result,$series3);

		$jsonstring = json_encode($result, JSON_NUMERIC_CHECK);
		//$jsonstring = json_encode($result);
		//$jsonstring = '[{"name":"Month","data":["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"]},{"name":"Wordpress","data":[4,5,6,2,5,7,2,1,6,7,3,4]},{"name":"CodeIgniter","data":[5,2,3,6,7,1,2,6,6,4,6,3]},{"name":"Highcharts","data":[7,8,9,6,7,10,9,7,6,9,8,4]}] ';
			if ($_GET["prev"] == 1){
				include 'savejson.php';
			}
		}
	
			
			
	$callback = $_GET['callback'];
	echo $callback.'('.$jsonstring.');';
	//echo $jsonstring;
		
	}
} else {
	echo "ERROR";
}
1
?>