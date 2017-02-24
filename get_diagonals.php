<?php

//////////////////////////////////////////////////////////////////////////
//diagonals - rotate datas so that birthyear becomes a series plotted against year 
//and another value, requires splitting age ranges into individual years

//load the database class
require_once 'class.mysql.php';
require_once 'cohort_convert_library.php';

//options for computing the diagonal
	$remove_partial_lines = false;
	
/////////////////////////////////////	
//titling
session_start();
$page_title = "diagonals";
include("header.php");
echo "<h2>Cohort Convert</h2>\n";	
include("navigation.php");
echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
	if($DEBUG2 == true) 
		echo "<h3>DEBUG MODE - Level 2</h3>\n";
	else if($DEBUG == true) 
		echo "<h3>DEBUG MODE</b> - Level 1</h3>\n";
echo "Results for diagonalizing data from age-series versus age to birthyear versus age.";
echo "</div>\n";

//beginning of printed output
echo "<!--program output-->\n<div id=\"output\">\n";

/////////////////////////////////////	
//security checks
//http://us2.php.net/manual/en/book.filter.php
//http://us2.php.net/manual/en/filter.constants.php
//confirm data is present for a dataset
	$datasets_id = filter_input(INPUT_POST, 'datasets', FILTER_VALIDATE_INT);
	$type        = filter_input(INPUT_POST, 'diagonals_options', FILTER_SANITIZE_STRING);
	$decimals    = filter_input(INPUT_POST, 'decimals', FILTER_VALIDATE_INT);
	
//////////////////////////////////////
//input data from form	
	if($DEBUG) debug_text_print("dataset=$datasets_id and type = $type and decimals = $decimals");

//confirm that fitted curve data is present for a dataset
//the above defaults were set - change with $db->init_info($host, $user, $pwd, $db_name, $db_tbl);
	$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
	$db->connect_default();
	$db->query('SET storage_engine=MyISAM');

//using the datasets_id, recover the fitted-curve data
	$query_string = "SELECT * FROM $db_tbl_ageseries as s INNER JOIN $db_tbl_curvefit as i ON i.age_series_id = s.ID WHERE datasets_id='".$datasets_id."'"."ORDER BY i.year, s.age_start, s.age_end";
	
//run the query
	$fetch_result = $db->query($query_string);

	if($DEBUG) debug_text_print("<b>QUERY:</b> $query_string");
	if($DEBUG2) echo "<b>RESULT:</b> $fetch_result";	
	
//begin writing primary output
	
//2D matrix holding the joined table with birthyears (y axis, converted from age bands to years between 
//the first and last year year of the original age band data series. 
//Missing birthyears have their y values interpolated.
	$birthyear_list   = array();
	$dataset_count      = 0;
	
//an array with all the unique age_series_id values - use to create additional entries for 
//missing birthyears
	$series_match       = false;
	$series_count       = 0;
	
//get the unique series list
		if($fetch_result) {
			while ($row = $db->fetch_array($fetch_result)) {
//process into a set of arrays, each member in the array has the same age_series_id
				$birthyear_list[$dataset_count] = array(
											year           => $row['year'],
											value          => number_format($row['value'], $decimals,'.',''),
											age_start      => $row['age_start'],
											age_end        => $row['age_end'],
											age_midpoint   => round(($row['age_end'] + $row['age_start'])/2),
											age_series_id  => $row['age_series_id'],
											primary_value  => true, //indicate that this is real, not interpolated data
											original_value => $row['primary_value'],
											dataset        => $row['datasets_id'],
											);

				$dataset_count++;
				}
			}
			
/////////////////////////////////////
//compute the length
	$birthyear_list_length = count($birthyear_list);
	
/////////////////////////////////////
//check birthyear matrix
	if($DEBUG) array_print_2d($birthyear_list, "birthyear_list");
	
/////////////////////////////////////
//expanded data
	if($DEBUG) debug_text_print("Expanding series points to individual ages...");
	
	$series_list_expanded = array();

/////////////////////////////////////
//find pairs of series on top of each other
	for($i = 0; $i < $birthyear_list_length - 1; $i++) {
	
//get the top series
		$top_series     = $birthyear_list[$i];
//dummy mid series
		$mid_series     = $birthyear_list[$i];
//get the bottom series
		$bottom_series  = $birthyear_list[$i + 1];
		
//create new array elements between the top and bottom
		$begin          = $top_series['age_midpoint'];
		$end            = $bottom_series['age_midpoint'];
		$age_series_id  = $top_series['age_series_id'];
		$age_band_width = $end - $begin;
		$begin_val      = $top_series['value'];
		$end_val        = $bottom_series['value'];
	
		if($age_band_width < 0) { //end of array, goes negative as it loops around to next value
			if($DEBUG2) echo $top_series['age_start']." ".$top_series['age_midpoint']." ".$top_series['age_end']."<br />";
			$end = $top_series['age_midpoint'];
			$age_band_width = $end - $begin + 1;
			$end_val = $begin_val;
			}
	
		if($DEBUG2) echo "age_series_id=$age_series_id begin=$begin and end=$end begin_val=$begin_val and end_val=$end_val and mid=$mid age_band_width=$age_band_width<br />";
		
//create new records
		for($j = 0; $j < $age_band_width; $j++) {
			$new_age = $begin + $j;
			$new_value = round(interpolate_xy_linear($begin, $begin_val, $end, $end_val, $new_age),2);
			if($DEBUG2) echo "new age=$new_age begin_val=$begin_val end_val=$end_val new_val=$new_value<br />";
			$mid_series['age_midpoint']       = $new_age;
			$mid_series['birth_year']         = $mid_series['year'] - $new_age;
			$mid_series['value']              = $new_value;
			if($new_age == $begin) 
				$mid_series['primary_value']  = true;
			else
				$mid_series['primary_value']  = false;
			if($j != 0) 
				$mid_series['original_value'] = false;
			$series_list_expanded[] = $mid_series;
			
//tag the records as valid - some won't be
			
			}

		}
	
	$series_list_expanded_length = count($series_list_expanded);
		
/////////////////////////////////////
//check the expanded matrix
	if($DEBUG) {
		array_print_2d($series_list_expanded, "EXPANDED SERIES DATA");
		}
		
//get a list of all the UNIQUE values at a particular key 
//in the sub-arrays
	$xvals = array();
	$xvals = array2d_unique1d($series_list_expanded, 'year');

//get the y-values for each series
//make a 2d array, with a unique value of 'title' as the 
//1d key, and a blank array as 2D key
//if there is more than one record in the input with the same 'title' it is ignored
	$yvals = array();
	
//this will create a new 2D array using a key from one of the second dimension array elements
//$yvals = array2d_rekey_chart($year_matrix, 'title', 'title', 'value');
	$yvals = array2d_rekey_chart($series_list_expanded, 'birth_year', 'value');

//yvals should now be a multi-dmensional array, with the first dimension keys 
//the names of the series, and the sub-arrays a list of y-values for each series
	array_print_2d($yvals, "Y values");
	
//////////////////////////////////////
//plot current array
	plot_y_series($xvals, $yvals, $legend_x, $legend_y, $title, $title_short, "input");
	
	
//////////////////////////////////////
//option to remove partial lines (where the birthyear is too early or too late to appear in the original 
//age bands.

/////////////////////////////////////
//output to database

//first, delete any old dataset
	$query_string = "DELETE FROM input_diagonals WHERE datasets_id='".$datasets_id."'";
	$fetch_result = $db->query($query_string);
	
//create a mysql-fommat date-time
	$curr_time = date("Y-m-d H:i:s", time());

//now, write our records from the array into the table
	if($DEBUG) debug_text_print("Writing new table to database...");
	$write_count = 0;

	for($i = 0; $i < $series_list_expanded_length; $i++) {
		$point_array = $series_list_expanded[$i]; //get an individual record

		$query_string = "INSERT INTO $db_tbl_diagonals 
			(year,age_series_id,birth_year,value,date_created,date_modified,datasets_id,primary_value,original_value,interpolation_type) VALUES (".
			"'".$point_array['year']."',".
			"'".$point_array['age_series_id']."',".
			"'".$point_array['birth_year']."',".
			"'".$point_array['value']."',".
			"'".$curr_time."',".
			    "CURRENT_TIMESTAMP".",".
			"'".$datasets_id."',".
			"'".$point_array['primary_value']."',".
			"'".$point_array['original_value']."',".
			"'".$type."')";
					
			if($DEBUG2) array_print_1d($point_array, "index=$j");
					
			$fetch_result = $db->query($query_string);
			if($fetch_result == 1)
				$write_count++;
				
			if($DEBUG2) echo "FETCH RESULT: $fetch_result<br />";

			}
			

/////////////////////////////////////
//clean up		
	if($DEBUG) debug_text_print("Age-padding of series complete, $write_count records written to $db_tbl_diagonals");
	
$_SESSION['stage']    = STAGE_DIAGONAL; //we are at stage login
echo "</div>\n";
include("footer.php");

//EXTRA
//sort the array by age_series_id
//function cmp($a, $b) {
 //   return strcmp($a['age_series_id'], $b['age_series_id']);
//	}
//	usort($birthyear_list, "cmp");


?>