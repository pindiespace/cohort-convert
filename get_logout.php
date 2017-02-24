<?php

/////////////////////////////////////
//log out of a session

//calling from navigation, which means this script is invoked directly, instead
//of included in a larger script - so we have to start a session
//$logout_msg = "" if called from navigaton
//$logout_msg = something if called elsewhere
if(empty($logout_msg))
	session_start();

//load the database class
require_once 'cohort_convert_library.php';

//if the user is logged in, delete the session variables to log them out
	if(isset($_SESSION['ID'])) {
		$_SESSION = array(); //clear the array
		}
	
//delete the session cookie by setting its expiration to an hour ago (3600)
	if(isset($_COOKIE[session_name()])) {
		setcookie(session_name(), "", time() - 3600);
		}
	
//finally, destroy the session
	if(isset($_SESSION)) {
		session_destroy();
		}
	
	$title = "Cohort Covert - Logout";
	include("header.php");
	echo "<h2>Cohort Convert - Logout</h2>\n";
	include("navigation.php");
	echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
	if($DEBUG2 == true) 
		echo "<h3>DEBUG MODE - Level 2</h3>\n";
	else if($DEBUG == true) 
		echo "<h3>DEBUG MODE</b> - Level 1</h3>\n";
	echo "</div>\n";
//beginning of printed output
	echo "<!--program output-->\n<div id=\"output\">\n";
	if(!empty($logout_msg))
		echo "Results of your logout - $logout_msg";
//debug_text_print("You are currently logged out");
		include("reg_module.php");
		echo "</div>\n";
		include("footer.php");


?>