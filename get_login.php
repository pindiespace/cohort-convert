<?php
//process user login

//load classes used by this program
require_once 'class.mysql.php';
require_once 'cohort_convert_library.php';

/////////////////////////////////////
//variables
$logout_msg = "not currently logged in";

/////////////////////////////////////
//start a session
session_start();

/////////////////////////////////////	
//titling
//CAN'T BE HERE DUE TO SESSSON

//we only use this here
$db_tbl_accounts = 'accounts';
$db_tbl_datasets = 'datasets';

/////////////////////////////////////	
//security checks
//http://us2.php.net/manual/en/book.filter.php
//http://us2.php.net/manual/en/filter.constants.php
//confirm data is present for a dataset
	$username  = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
	$password  = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
	
//////////////////////////////////////
//input data from form	
	if($DEBUG2) debug_text_print("username=$username and password=$password");
	
	if(!empty($username) && !empty($password)) {
	
/////////////////////////////////////	
//get the dataset and load it into an array
//the above defaults were set - change with $db->init_info($host, $user, $pwd, $db_name, $db_tbl);
		$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
		$db->connect_default();
		$db->query('SET storage_engine=MyISAM');
		
//get the dataset name
		$query_string = "SELECT * from $db_tbl_accounts where username='$username' AND password='$password'";
		$fetch_result = $db->query($query_string);
	
		if(mysql_num_rows($fetch_result)) {
//found the user (there can only be one)
		$row = $db->fetch_array($fetch_result);
//set a session variable
			$_SESSION['ID']       = $row['ID'];  //we use the primary key as our local session variable
			$_SESSION['stage']    = STAGE_LOGIN; //we are at stage login
			
//set our default dataset variable
		$query_string = "SELECT * FROM  $db_tbl_datasets WHERE accounts_id='".$row['ID']."'";
		$fetch_result = $db->query($query_string);
		
//there can be multiple datasets, but we only want to confirm that there are at least one
//we select the first one in the list
		if(mysql_num_rows($fetch_result)) {
			$row = $db->fetch_array($fetch_result);
			if(isset($row))
				$_SESSION['dataset_id'] = $row['ID']; //first dataset associated with this user
			}
		
//begin constructing our login status page
			session_start();
			$title = "Login Page";
			include("header.php");
			echo "<h2>Cohort Convert - Login Result</h2>\n";	
			include("navigation.php");
			echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
			if($DEBUG2 == true) 
				echo "<h3>DEBUG MODE - Level 2</h3>\n";
			else if($DEBUG == true) 
				echo "<h3>DEBUG MODE</b> - Level 1</h3>\n";
			echo "Results of plotting data, bundling individual age cohorts into generations, plotted by year";
			echo "</div>\n";

//beginning of printed output
			echo "<!--program output-->\n<div id=\"output\">\n";
			echo "<p>Your login was successful</p>";
			include("option_module.php");
			echo "</div>\n";
			include("footer.php");
			}
//didn't find the user
		else {
			$logout_msg = "Your username and/or password was invalid - please check or register";
			include('get_logout.php');
			} //end of invalid name/password
	
		} //end of data in input fields
	else {
		$logout_msg = "No information supplied by user - try again";
		include('get_logout.php');
		}
	

?>