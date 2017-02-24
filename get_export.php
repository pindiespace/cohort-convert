<?php

/////////////////////////////////////
//export data

//load classes used by this program
require_once 'class.mysql.php';
require_once 'cohort_convert_library.php';

//since our output might not include a web page, include the common variables here
include("common.php");

/////////////////////////////////////
//this flag determines whether we download directly, or provide a link to download
	$output_mode = 'file'; //'file' or 'download'
	$output_mode = 'download'; 

/////////////////////////////////////	
//titling
session_start();

if($output_mode == 'file') { 
	$page_title = "Export Table Data";
	
		} //end of output mode == file
	else {
//no header, so must include common variables separately
		include("common.php");
		$DEBUG = false;
		}
		
		
	
/////////////////////////////////////	
//security checks
//http://us2.php.net/manual/en/book.filter.php
//http://us2.php.net/manual/en/filter.constants.php
//confirm data is present for a dataset
	$datasets_id    = filter_input(INPUT_POST, 'datasets', FILTER_VALIDATE_INT);
	$db_table_name  = filter_input(INPUT_POST, 'data_table', FILTER_SANITIZE_STRING);
	$export_options = filter_input(INPUT_POST, 'export_options', FILTER_SANITIZE_STRING);
	$decimals       = filter_input(INPUT_POST, 'decimals', FILTER_VALIDATE_INT);
	
	if($DEBUG) debug_text_print("<b>Form Input:</b><br />dataset=$datasets_id and data_table = $db_table_name and export_options = $export_options and decimals = $decimals");

	if($output_mode == 'file') debug_text_print("<b>EXPORTING $table_name in $export_options format</b>");
	
/////////////////////////////////////	
//get the dataset and load it into an array
//the above defaults were set - change with $db->init_info($host, $user, $pwd, $db_name, $db_tbl);
	$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
	$db->connect_default();
	$db->query('SET storage_engine=MyISAM');
		
//get the dataset name
	$query_string = "SELECT * from $db_tbl_datasets where ID='".$datasets_id."'";
	$fetch_result = $db->query($query_string);

//there can only be one dataset record
	$row = $db->fetch_array($fetch_result);
	
	$dataset_name  = $row['title'];
	$author        = $row['author'];
	$legend_x      = $row['legend_x'];
	$legend_y      = $row['legend_y'];
	$accounts_id   = $row['accounts_id'];
	
	if($DEBUG2) echo "title=$dataset_name and author=$author and legend_x=$legend_x and legend_y=$legend_y and accounts_id=$accounts_id<br />";
	
//get additional information from the user's account
	$query_string  = "SELECT * from $db_tbl_accounts where ID='".$accounts_id."'";
	$fetch_result = $db->query($query_string);

//there can be only one account record
	$row = $db->fetch_array($fetch_result);
	
	$account      = $row['username'];
	$organization = $row['organization'];
	
	if($DEBUG2) echo "account=$account and organization=$organization<br />";

///////////////////////////////////////
//run the query to get the records to export
	$query_string    = "SELECT * from $db_table_name where datasets_id='".$datasets_id."'";
	if($DEBUG) debug_text_print("query string: $query_string<br />");
		
	$fetch_result  = $db->query($query_string);
	
////////////////////////////////////////
//if we didn't get any records back, exit
	if(!mysql_num_rows($fetch_result)) {
		if($output_mode != 'file') {
				include("header.php");
	echo "<h2>Cohort Convert - Export data as an Excel file</h2>\n";

	include("navigation.php");
	echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
	if($DEBUG2 == true) 
		echo "<h3>DEBUG MODE - Level 2</h3>\n";
	else if($DEBUG == true) 
		echo "<h3>DEBUG MODE</b> - Level 1</h3>\n";
	echo "Output Table data as a server-side file.";
	echo "</div>\n";

//beginning of printed output
	echo "<!--program output-->\n<div id=\"output\">\n";
//////////////////////////////////////
//write the debug level to output
	if($DEBUG2 == true) 
		echo "<h3>DEBUG MODE - Level 2</h3>";
	else if($DEBUG == true) 
		echo "<h3>DEBUG MODE</b> - Level 1</h3>";
			echo "<h2>Cohort Convert - Export data failed</h2>\n";
			}
		debug_text_print("No records to output in table <b>$db_table_name</b>");
		if($output_mode != 'file')
			include("footer.php");
		exit;
		}
		
	$column_titles = array();
	$column_values = array();
	
	if($fetch_result) {
		while($row = $db->fetch_array($fetch_result)) {
			
//attach individual column values, removing those we don't want
			$new_row = array();
			foreach($row as $key => $value) {
				if(!is_numeric($key)) {
//we normally don't include database-specific fields, like creation or modification dates
					switch($key) {
						case 'date_created':
							break;
						case 'date_modified':
							break;
						case 'dataset_id':
							break;
						case 'age_series_id':
							$query_string = "SELECT * from $db_tbl_ageseries WHERE ID='$value'";
							$fetch_result2 = $db->query($query_string);
							$row2 = $db->fetch_array($fetch_result2);
							$new_row[] = $row2['title'];
							break;
						case 'generation_id':
							$query_string = "SELECT * from $db_tbl_generations WHERE ID='$value'";
							$fetch_result2 = $db->query($query_string);
							$row2 = $db->fetch_array($fetch_result2);
							$new_row[] = $row2['generation_name'];
							break;
						default:
							/////////////////////////////////$new_row[] = $value;
							$new_row[] = number_format($value, $decimals, '.','');
							break;
						}
					}
				}
				
			$column_values[] = $new_row;
			} //end of while loop
			unset($new_row);
			
			
		} //end of valid fetch_result
		
		//array_print_2d($column_values);
		

///////////////////////////
//CURRENTLY - we are using an XML dump that can be interpreted by Excel
//link to true Excel file (OLE) dump - http://pear.php.net/package/Spreadsheet_Excel_Writer
//required OLE package - http://pear.php.net/package/OLE
//our array above has items at the following second-dimension indices
//series_id = 2
//year      = 1
//value     = 3
//array[][] = 'year' 'series_id' 'value'
//convert to
//array[series_id] year1, year2, year3.... (values from original 'year' field)
//so, we'll get a 2D array, first dimension = series_id, second dimension = year
	////////////////array_print_2d($column_values, "column values");
	
	$new_arr = array2d_rekey($column_values, 2, 1, 3);
	if($DEBUG) array_print_2d($new_arr, "new array");			
///////////////////////////
//start of specific outputs
//re-key the arrays for output in the right x-y format		
	switch($db_table_name) {
		case 'input':
			break;
		
		case 'input_curvefit':			
			break;
			
		case 'input_diagonals':
			array_print_2d($new_arr, "new array");
			break;
			
		case 'output_bundle':
			array_print_2d($new_arr, "new array");
			break;
			
		default:
			break;
		}
	
	write_xls($new_arr, $column_titles, $db_table_name, $dataset_name, $legend_x, $legend_y, $account, $organization, $output_mode);	

///////////////////////////////////////
//clean up
	unset($db);
			
	if($output_mode == 'file') {
//no change in computation state
		echo "</div>\n";
		include("footer.php");
		}
	

?>