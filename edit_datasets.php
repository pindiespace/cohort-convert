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
$title = "Edit or Delete Dataset";
include("header.php");
echo "<h2>Cohort Convert - Edit or Delete a Dataset</h2>\n";	
include("navigation.php");
echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
	if($DEBUG2 == true) 
		echo "<h3>DEBUG MODE - Level 2</h3>";
	else if($DEBUG == true) 
		echo "<h3>DEBUG MODE</b> - Level 1</h3>";

echo "Edit a Dataset. You may alter the values listed. Existing computations will remain linked to this dataset";
echo "</div>\n";

//beginning of printed output
echo "<!--program output-->\n<div id=\"output\">\n";
/*
//the table(s) we are using
	$db_tbl_datasets          = 'datasets';
	$db_tbl_input             = 'input';
	$db_tbl_curvefit          = 'input_curvefit';
	$db_tbl_diagonals         = 'input_diagonals';
	$db_tbl_generations       = 'generations';
	$db_tbl_generations_model = 'generations_model';
	$db_tbl_bundles           = 'output_bundles';
*/

//security test for input
if(isset($_POST)) {

	$datasets_id     = filter_input(INPUT_POST, 'datasets', FILTER_VALIDATE_INT);
	$datasets_option = filter_input(INPUT_POST, 'datasets_option', FILTER_SANITIZE_STRING);
	
//look for enough input
	if(!empty($datasets_id) && !empty($datasets_option)) {

//open the datasets table
		$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
		$db->connect_default();
//  echo '<li> Connect to the database succesfully </li>';
//  echo '<li> DB Charset: '.$db->valid_charset.' </li>';
  
		$db->query('SET storage_engine=MyISAM');
		
		switch($datasets_option) {
			
				case 'edit': //create a new dataset without any other tables
					break;
			
				case 'delete':
					$query_string = "DELETE FROM $db_tbl_datasets WHERE ID='$datasets_id'";
					if($DEBUG) print "Delete - query string is $query_string<br />";
					$fetch_result = $db->query($query_string);
					if($fetch_result) {
						$query_string = "DELETE FROM $db_tbl_input WHERE datasets_id='$datasets_id'";
						$fetch_result = $db->query($query_string);
						if($DEBUG) print "Delete - query string is $query_string, result=".$fetch_result."<br />";
						
						$query_string = "DELETE FROM $db_tbl_curvefit WHERE datasets_id='$datasets_id'";
						$fetch_result = $db->query($query_string);
						if($DEBUG) print "Delete - query string is $query_string, result=".$fetch_result."<br />";
						
						$query_string = "DELETE FROM $db_tbl_diagonals WHERE datasets_id='$datasets_id'";	
						$fetch_result = $db->query($query_string);
						if($DEBUG) print "Delete - query string is $query_string, result=".$fetch_result."<br />";
												
						$query_string = "DELETE FROM $db_tbl_bundles WHERE datasets_id='$datasets_id'";
						$fetch_result = $db->query($query_string);
						if($DEBUG) print "Delete - query string is $query_string, result=".$fetch_result."<br />";
						}
				else {
						debug_text_print("ERROR dataset could not be deleted");
						}
					break;
			
				default:
					break;
				}
		
			} //end of required fields not empty
		else {
				debug_text_print("ERROR - NO DATA IN INPUT");
				}

	} //end of isset() test
	
	echo "</div>\n";
	include("footer.php");


?>
