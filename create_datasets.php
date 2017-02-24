<?php
//create a new dataset

//our debug mode variables
$DEBUG  = true;
$DEBUG2 = false;

//load the database class
require_once 'class.mysql.php';
require_once 'cohort_convert_library.php';

/////////////////////////////////////	
//titling
session_start();
$title = "Create Dataset";
include("header.php");
echo "<h2>Cohort Convert - Create a new dataset</h2>\n";	
include("navigation.php");
echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
	if($DEBUG2 == true) 
		echo "<h3>DEBUG MODE - Level 2</h3>";
	else if($DEBUG == true) 
		echo "<h3>DEBUG MODE</b> - Level 1</h3>";
		
echo "Create a new dataset.";
echo "</div>\n";

//beginning of printed output
echo "<!--program output-->\n<div id=\"output\">\n";

//security test for input
if(isset($_POST)) {

	$datasets_id  = filter_input(INPUT_POST, 'datasets', FILTER_VALIDATE_INT);
	$author       = filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING);
	$title        = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
	$title_short  = filter_input(INPUT_POST, 'title_short', FILTER_SANITIZE_STRING);
	$legend_x     = filter_input(INPUT_POST, 'legend_x', FILTER_SANITIZE_STRING);
	$legend_y     = filter_input(INPUT_POST, 'legend_y', FILTER_SANITIZE_STRING);
	$generation   = filter_input(INPUT_POST, 'generation', FILTER_SANITIZE_STRING);
	
//look for enough input
	if(!empty($datasets_id) || !empty($author) || !empty($title) || !empty($title_short) || 
		!empty($legend_x) || !empty($legend_y) || !empty($generation)) {

//open the datasets table
		$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
		$db->connect_default();
//  echo '<li> Connect to the database succesfully </li>';
//  echo '<li> DB Charset: '.$db->valid_charset.' </li>';
  
		$db->query('SET storage_engine=MyISAM');
		
//make sure we don't have a dataset with this title	
		$query_string = "SELECT * from $db_tbl_datasets WHERE author='$author' AND title='$title' AND title_short='$title_short' AND legend_x='$legend_x' AND legend_y='$legend_y' and generation='$generation'";
		$fetch_result = $db->query($query_string);
		if(mysql_num_rows($fetch_result)) {
			debug_text_print("ERROR - dataset already in database");
			include("footer.php");	
			exit;
			}
		
//insert the dataset
			$query_string = "INSERT INTO $db_tbl (author, title, title_short, legend_x, legend_y, generation) VALUES ('$author', '$title', '$title_short', '$legend_x', '$legend_y', '$generation')";
			$fetch_result = $db->query($query_string);
			if($fetch_result) {
				 debug_text_print("new dataset inserted");
				}
			else {
				 debug_text_print("ERROR new dataset could not be inserted");
				}
		

//create the new record

		} //end of required fields not empty

	} //end of isset() test
	
	debug_text_print("ERROR - NO DATA IN INPUT");
//state of program unchanged by adding new dataset
	echo "</div>\n";
	include("footer.php");

?>
