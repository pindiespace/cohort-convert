<?php

//////////////////////////////////////////////////////////////////////////
//diagonals - rotate datas so that birthyear becomes a series plotted against year 
//and another value, requires splitting age ranges into individual years

//load the database class
require_once 'class.mysql.php';
require_once 'cohort_convert_library.php';

/////////////////////////////////////	
//titling
session_start();
$page_title = "Generation (Cohort) Bundles";
include("header.php");
echo "<h2>Cohort Convert - $title</h2>\n";
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

if($DEBUG2)
	echo "dec_point = '$dec_point' and thousands_sep  = '$thousands_sep'";
/////////////////////////////////////	
//security checks
//http://us2.php.net/manual/en/book.filter.php
//http://us2.php.net/manual/en/filter.constants.php
//confirm data is present for a dataset
	$datasets_id   = filter_input(INPUT_POST, 'datasets', FILTER_VALIDATE_INT);
	$generations_model_id = filter_input(INPUT_POST, 'generations', FILTER_VALIDATE_INT);
	$type          = filter_input(INPUT_POST, 'bundles_options', FILTER_SANITIZE_STRING);
	$decimals      = filter_input(INPUT_POST, 'decimals', FILTER_VALIDATE_INT);
	
//////////////////////////////////////
//input data from form	
	if($DEBUG) debug_text_print("dataset=$datasets_id and generations_id=$generations_model_id and type = $type and decimals = $decimals");

//confirm that fitted curve data is present for a dataset
//the above defaults were set - change with $db->init_info($host, $user, $pwd, $db_name, $db_tbl);
	$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
	$db->connect_default();
	$db->query('SET storage_engine=MyISAM');
	
	
//get all the information about the dataset
	$query_string = "SELECT * FROM $db_tbl_datasets where ID='".$datasets_id."'";
	$fetch_result = $db->query($query_string);
	$row = $db->fetch_array($fetch_result);
	
//save dataset chart legend information
	$accounts_id = $row['accounts_id'];
	$author      = $row['author'];
	$title       = $row['title'];
	$title_short = $row['title_short'];
	$legend_x    = $row['legend_x'];
	$legend_y    = $row['legend_y'];

//using the datasets_id, recover the generations data 
	$query_string = "SELECT * FROM $db_tbl_generations_model as s INNER JOIN $db_tbl_generations as i ON i.generations_model_id = s.ID WHERE generations_model_id='".$generations_model_id."'"."ORDER BY i.age_start, i.age_end";
	
//run the query
	$fetch_result = $db->query($query_string);

	if($DEBUG) debug_text_print("<b>QUERY:</b> $query_string");
	if($DEBUG2) echo "<b>RESULT:</b> $fetch_result";	
	
//begin writing primary output
//the following takes a series of values, expanded by individual ages, and bundles them into cohort bundles. This is plotted against time, usually years.
	
//2D matrix holding the joined table with birthyears (y axis, converted from age bands to years between 
//the first and last year year of the original age band data series. 
//Missing birthyears have their y values interpolated.
	$generations_list       = array();
	$generations_count      = 0;
	
//get the unique series list
		if($fetch_result) {
			while ($row = $db->fetch_array($fetch_result)) {
//process into a set of arrays, each member in the array has the same age_series_id
				$generations_list[$generations_count] = array(
											name           => $row['generation_name'],
											age_start      => $row['age_start'],
											age_end        => $row['age_end'],
											age_midpoint   => round(($row['age_end'] + $row['age_start'])/2),
											generation_id  => $row['ID'],
											generations_model_id  => $row['generations_model_id'],
											);

				$generations_count++;
				}
			}
			
	$generations_count = count($generations_list);
	
//////////////////////////////////////
//sort by starting birthyear
//sort this list by year
function cmp2($a, $b) {
    return strcmp($a['age_start'], $b['age_start']);
	}
	usort($generations_list, "cmp2");

/////////////////////////////////////
//find the high and low birthyears for all generations in the set
	$oldest_birthyear  = $generations_list[0]['age_start'];
	$highest_birthyear = $generations_list[$generations_count -1]['age_end'];
	
/////////////////////////////////////
//check birthyear matrix
	if($DEBUG) array_print_2d($generations_list, "generations_list");
	
/////////////////////////////////////
//get the diagonals values for a particular dataset
	if($DEBUG) debug_text_print("Retrieving dataset points...");
	
	$diagonals_list        = array();
	$diagonals_list_count = 0;
	
//using the datasets_id, recover the diagonals data 
	$query_string = "SELECT * FROM $db_tbl_diagonals WHERE datasets_id='".$datasets_id."'"." ORDER BY birth_year, year";
	
//run the query
	$fetch_result = $db->query($query_string);

	if($DEBUG) debug_text_print("<b>QUERY:</b> $query_string");
	if($DEBUG2) echo "<b>RESULT:</b> $fetch_result";	
	
//put the diagonals data into an array
		if($fetch_result) {
			while ($row = $db->fetch_array($fetch_result)) {
//process into a set of arrays, each member in the array has the same age_series_id
				$diagonals_list[$diagonals_list_count] = array(
											birth_year      => $row['birth_year'],
											year            => $row['year'],
											age             => $row['year'] - $row['birth_year'],
											value           => number_format($row['value'], $decimals,'.',''),
											primary_value   => $row['primary_value'],
											orginal_value   => $row['original_value'],
											interpolation_type => $row['interpolation_type'],
											);
											$interpolation_type = $row['interpolation_type']; //use for final output

				$diagonals_list_count++;
				}
			}

	$diagonals_list_count = count($diagonals_list);

	if($DEBUG) array_print_2d($diagonals_list, "diagonals_list");
	
//////////////////////////////////////
//get a list of all the unique years (x axis) that will be in the final output
	$year_list                = array();
	$found_flag               = false;
	
	foreach($diagonals_list as $point_array) {
		$found_flag = false;
		foreach($year_list as $value) {
			if($point_array['year'] == $value)
			$found_flag = true;
			}
		if($found_flag == false) { //add new year to list
			$year_list[] = $point_array['year'];
			}
		}
		
//sort by year
	asort($year_list, SORT_NUMERIC);
		
	array_print_1d($year_list, $arr_title);
	

////////////////////////////////////////
//define an array that will combine and average data from the diagonals under 
//a specific generation
	$generations_bundle       = array();
	$generations_bundle_count = 0;
	
	foreach($year_list as $years) { //for every year in the series
	
		foreach($generations_list as $generation) {
			$generations_bundle[] = $generation;
			$generations_bundle_count = count($generations_bundle);
			$generations_bundle[$generations_bundle_count - 1]['datasets_id'] = $datasets_id;
			$generations_bundle[$generations_bundle_count - 1]['year'] = $years;
			$generations_bundle[$generations_bundle_count - 1]['total'] = 0;
			$generations_bundle[$generations_bundle_count - 1]['average_value'] = 0;
			$generations_bundle[$generations_bundle_count - 1]['std_dev'] = 0;
			$generations_bundle[$generations_bundle_count - 1]['midpoint_dev'] = 0;
			$generations_bundle[$generations_bundle_count - 1]['num_entries'] = 0;
			$generations_bundle[$generations_bundle_count - 1]['center_dist'] = round(($generation['age_end'] - $generation['age_start'])/2);
//define a special sub-array to hold individual values for statistics
			$generations_bundle[$generations_bundle_count - 1]['value_list'] = array();
			}
		}

//sort this list by year
function cmp($a, $b) {
    return strcmp($a['generation_id'], $b['generation_id']);
	}
	usort($generations_bundle, "cmp");

	$generations_bundle_count = count($generations_bundle);
	
/////////////////////////////////////////
//check generations bundle matrix
	if($DEBUG) array_print_2d($generations_bundle, "Generations Bundle");
	
/////////////////////////////////////////
//now go through the diagonals dataset and begin adding values 
//to the generations bundle array. Compute statistics
	foreach($diagonals_list as $point_array) {
		$birth_year = $point_array['birth_year'];
		$value      = $point_array['value'];
		$year       = $point_array['year'];
		for($i = 0; $i < $generations_bundle_count; $i++) {
			if($birth_year >= $generations_bundle[$i]['age_start'] && $birth_year <= $generations_bundle[$i]['age_end'] && $year == $generations_bundle[$i]['year']) {
				//add the info
				$generations_bundle[$i]['num_entries']++;
				$generations_bundle[$i]['total'] += $point_array['value'];
//				$temp_val_array = $generations_bundle[$i]['value_list'];
//				$temp_val_array[] = $point_array['value'];
//				$generations_bundle[$i]['value_list'] = $temp_val_array;
				$generations_bundle[$i]['value_list'][] = $point_array['value'];
				}
			}
		}
		

//release the original data
	unset($diagonals_list);

////////////////////////////////////////////////////////////////////
//sequential sort of output array
//1. sort on generation_id
//2. sort on year within a generation id series
	$gen_ids                   = array();
 	$gen_ids                   = array2d_unique1d($generations_bundle, 'generation_id');
	$gen_ids_count             = count($gen_ids);
	$generations_series_sorted = array();
//pull out the records, assign to new array based on series	
	for($i = 0; $i < $generations_bundle_count; $i++) {
		for($j = 0; $j < $gen_ids_count; $j++) {
			if($generations_bundle[$i]['generation_id'] == $gen_ids[$j])
				$generations_series_sorted[$gen_ids[$j]][] = $generations_bundle[$i];
			}
		}
		
//sort individual series
	foreach($generations_series_sorted as $key => $value) {
		$generations_series_sorted[$key] = array2d_sort($value, 'year');
		}
		
//copy back to big array
	unset($generations_bundle);	
	$generations_bundle = array();
	foreach($generations_series_sorted as $key => $value) {
		foreach($value as $key2 => $value2) {
			$generations_bundle[] = $value2;
			}
		}	


/////////////////////////////////////
//calculate statistics for the bundle
	for($i = 0; $i < $generations_bundle_count; $i++) {
		if($generations_bundle[$i]['num_entries'] != 0)
			$generations_bundle[$i]['average_value'] = round($generations_bundle[$i]['total']/$generations_bundle[$i]['num_entries'], 2);
			$generations_bundle[$i]['std_dev'] =  round(standard_deviation_population($generations_bundle[$i]['value_list'], $generations_bundle[$i]['average_value']),2);
		}

/////////////////////////////////////
//check the array
	if($DEBUG) array_print_2d($generations_bundle, "generations_bundled with values");
		
/////////////////////////////////////
//write the results to output
array_print_2d($generations_bundle, "generations_bundled with values");

	foreach($generations_bundle as $bundle) {
	$query_string = "INSERT INTO $db_tbl_bundles (year, generation_id, average_value, std_dev, birthyear_start, birthyear_end, datasets_id, interpolation_type) VALUES(".
				"'".$bundle['year']."',".																															 				"'".$bundle['generation_id']."',".																															 				"'".$bundle['average_value']."',".																									  				"'".$bundle['std_dev']."',".																																																																																												   				"'".$bundle['birthyear_start']."',".
				"'".$bundle['birthyear_end']."',".
				"'".$bundle['datasets_id']."',".
				"'".$interpolation_type."'".
				")";
	
			$fetch_result = $db->query($query_string);
			}

/////////////////////////////////////
//chart of results
//save dataset chart legend information
////	$title       = $row['title'];
//////	$title_short = $row['title_short'];
/////	$legend_x    = 'year';
/////	$legend_y    = 'generation';
	
//get a list of all the UNIQUE values at a particular key 
//in the sub-arrays
	$xvals = array();
	$xvals = array2d_unique1d($generations_bundle, 'year');

//sort our x values so they are the same as our series data
	sort($xvals);
	array_print_1d($xvals, "X values");
	
//get the y-values for each series
//return only one record of each value of 'title'
//make a 2d array, with each value of 'title' as the 
//1d key, and an array of averaged values as a un-named list array
	$yvals = array();
	$yvals = array2d_rekey_chart($generations_bundle, 'name', 'average_value');

//////////////////////////////////////
//plot current array
	echo "TITLE IS $title and TITLE_SHORT IS $title_SHORT<br />";
	
	plot_y_series($xvals, $yvals, $legend_x, $legend_y, $title, $title_short, "Generational Plot");
	
	
/////////////////////////////////////
//clean up		
	if($DEBUG) debug_text_print("Bundling of series complete, $write_count records written to $db_tbl_bundles");
	
$_SESSION['stage']    = STAGE_BUNDLE; //we are at stage login
echo "</div>";
include("footer.php");


?>