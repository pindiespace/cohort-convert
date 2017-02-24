<?php
//////////////////////////////////////////////////////////////////////////
//registratoin - register a new user (non-admin)
//this user can only see their own data

//load classes used by this program
require_once 'class.mysql.php';
require_once 'cohort_convert_library.php';

$admin  = false; //can't become an admin this way

/////////////////////////////////////	
//titling
session_start();
$title = "Generation (Cohort) Bundles";
include("header.php");
echo "<h2>Cohort Convert - Results of Your Registration</h2>\n";	
include("navigation.php");
echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
	if($DEBUG2 == true) 
		echo "<h3>DEBUG MODE - Level 2</h3>\n";
	else if($DEBUG == true) 
		echo "<h3>DEBUG MODE</b> - Level 1</h3>\n";
echo "Results for your registration...";
echo "</div>\n";

//beginning of printed output
echo "<!--program output-->\n<div id=\"output\">\n";
/////////////////////////////////////	
//security checks
//http://us2.php.net/manual/en/book.filter.php
//http://us2.php.net/manual/en/filter.constants.php
//confirm data is present for a dataset

if(isset($_POST)) {

	$name         = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
	$organization = filter_input(INPUT_POST, 'organization', FILTER_SANITIZE_STRING);
	$email        = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
	$username     = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
	$password     = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
	$password2    = filter_input(INPUT_POST, 'password2', FILTER_SANITIZE_STRING);
	
	if($DEBUG2) print "name=$name and organization=$organizaton and email=$email and username=$username and password=$password and password2=$password2<br >";
	
	if(!empty($name) && !empty($organization) && !empty($email) && !empty($username) && 
		!empty($password) && !empty($password2)) {
		
		debug_text_print("Checking for duplicate usernames...");
//open the database, and check for an identical entry
//confirm that fitted curve data is present for a dataset
//the above defaults were set - change with $db->init_info($host, $user, $pwd, $db_name, $db_tbl);
		$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
		$db->connect_default();
		$db->query('SET storage_engine=MyISAM');
	
	
//get all the information about the dataset
		$query_string = "SELECT * FROM $db_tbl_accounts where username='$username'";
		$fetch_result = $db->query($query_string);
		if(mysql_num_rows($fetch_result)) {
//username already in database
			debug_text_print("Sorry, this username is already in the database - please choose another");
			}
		else {
//if not identical, add the new user
/////////////////////////////////////////
//we are going to create a dataset - get the time
//create a mysql-fommat date-time
			$curr_time = date("Y-m-d H:i:s", time());
			$query_string = "INSERT INTO $db_tbl_accounts (username, password, name, organization, email, date_created, admin) VALUES('$username', '$password', '$name', '$organization', '$email', '$curr_time', '$admin')";
			$fetch_result = $db->query($query_string);
			if($fetch_result)
				debug_text_print("Your're registered!");
			else
				debug_text_print("Registration failed, contact administrator");
			}
		
		}
	else {
		if($DEBUG) debug_text_print("Your input was incomplete - please re-enter");
		}

	}
else {
	if($DEBUG) debug_text_print("Form was empty, please complete information");
	}

/////////////////////////////////////
//clean up		
	if($DEBUG2) debug_text_print("Registration script complete");
	
//no change to computation state
	echo "</div>\n";
	include("footer.php");

?>