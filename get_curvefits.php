<?php
//////////////////////////////////////////////////////////////////////////
//curvefit - add years to raw data to convert to smoothed curve
//default is linear, but regression may be invoked
//common variables

//load the database class
require_once 'class.mysql.php';
require_once 'cohort_convert_library.php';

//the table(s) we are using
//	$db_tbl_accounts    = 'accounts';
//	$db_tbl_ageseries   = 'age_series';
//	$db_tbl_datasets    = 'datasets';
//	$db_tbl_input       = 'input';
//	$db_tbl_curvefit    = 'input_curvefit';
	
//these are used to format numbers - REMOVE commas when placed in mysql
//	$dec_point      = '.';
//	$thousands_sep  = '';	

/////////////////////////////////////	
//titling
session_start();
$page_title = "Curve-Fit Age Series Data";
include("header.php");
echo "<h2>Cohort Convert - Add interpolated years to y = f(years) data</h2>\n";	
include("navigation.php");
echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
	if($DEBUG2 == true) 
		echo "<h3>DEBUG MODE - Level 2</h3>\n";
	else if($DEBUG == true) 
		echo "<h3>DEBUG MODE</b> - Level 1</h3>\n";
echo "Results for fitting age series versus age data, plotted as values versus year for each age series.";
echo "</div>\n";

//beginning of printed output
echo "<!--program output-->\n<div id=\"output\">\n";

/////////////////////////////////////	
//security checks
//http://us2.php.net/manual/en/book.filter.php
//http://us2.php.net/manual/en/filter.constants.php
//confirm data is present for a dataset
	$datasets_id = filter_input(INPUT_POST, 'datasets', FILTER_VALIDATE_INT);
	$type        = filter_input(INPUT_POST, 'curvefit_options', FILTER_SANITIZE_STRING);
	$decimals    = filter_input(INPUT_POST, 'decimals', FILTER_VALIDATE_INT);
	

	
	if($DEBUG) debug_text_print("<b>Form Input:</b><br />dataset=$datasets_id and type = $type and decimals = $decimals");
	
/////////////////////////////////////	
//get the dataset and load it into an array
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
	
//get account information
	$query_string = "SELECT * FROM $db_tbl_accounts where ID='".$accounts_id."'";
	$fetch_result = $db->query($query_string);
	$row = $db->fetch_array($fetch_result);
	
	$account     = $row['username'];
	$organization= $row['organization'];
	
//echo some of this information
	echo "<div><h3>$title ($title_short)</h3>";
	echo "<b>author:</b> $author under account: $username<br />";
	echo "<b>organization:</b> $organization</div><br />";
	

//get all the records for this dataset, also recover the start and end age number from the age_series table
	$query_string = "SELECT * FROM $db_tbl_ageseries as s INNER JOIN $db_tbl_input as i ON i.age_series_id = s.ID WHERE datasets_id=' ".$datasets_id."'"."ORDER BY i.year, s.age_start, s.age_end";
	
//run the query
	$fetch_result = $db->query($query_string);

	if($DEBUG) debug_text_print("<b>QUERY:</b><br /> $query_string");	
	if($DEBUG2) echo "<b>RESULT:</b> $fetch_result<br />";
	
//begin writing primary output

//2D matrix holding the joined table with years (x axis, expanded to include all years between 
//the first and last year. 
//Missing years have their y values interpolated
	$year_matrix   = array();
	$dataset_count = 0;
	
//an array with all the unique age_series_id values - use to create additional entries for 
//missing years
	$series_list   = array();
	$series_match  = false;
	$series_count  = 0;
	
	if($fetch_result) {
		while ($row = $db->fetch_array($fetch_result)) {
//get the input records for this dataset
		//	if($DEBUG) echo "<li>".$row['year'].",".$row['age_start'].",".$row['age_end'].",".$row['value']."</li>\n";
//process into a set of arrays, each member in the array has the same age_series_id
				$year_matrix[$dataset_count] = array(
											year        => $row['year'],
											value     => number_format($row['value'], $decimals, '.',''),
											age_start => $row['age_start'],
											age_end   => $row['age_end'],
											title     => $row['title'],
											series    => $row['age_series_id'],
											primary_value   => true, //indicate that this is real, not interpolated data
											);
/////////////////////
//figure out how many data series there are in this dataset.								
//each time we encounter a new age_series_id (now series), add its value to a temp array
				$series_flag  = true;
				foreach($series_list as $key => $value) {
					if($DEBUG2) echo "COMPARING year_matrix=".$year_matrix[$dataset_count]['series']." and value=$value<br />";
					if($value['series'] == $year_matrix[$dataset_count]['series']) {
						$series_flag = false;
						if($DEBUG2) echo "FLAG=FALSE<br />";
						}
					}
				if($series_flag == true) { //we found a previously unknown data series (age_series_id)
					if($DEBUG2) echo "<li>DETECTED NEW UNIQUE SERIES....";
					//$series_list[] = $year_matrix[$dataset_count]['series'];
					$series_list[] = $year_matrix[$dataset_count];
					$series_count  = count($series_list);
					$series_list[$series_count - 1]['title']   = $year_matrix[$dataset_count]['title'];
					$series_list[$series_count - 1]['value']   = 0;
					$series_list[$series_count - 1]['year']    = 0;
					$series_list[$series_count - 1]['primary_value'] = false;
					if($DEBHG2) echo "series_id=".$series_list[$series_count - 1]['series']." and series_list age start=".$series_list[$series_count - 1]['age_start']."</li>";
					}
					
/////////////////////////////////////	
//check output
				if($DEBUG2) {
					echo "<li>curr: ".$year_matrix[$dataset_count]."</li>";
					$bobo = $year_matrix[$dataset_count];
					echo "<li>curr: ".sizeof($bobo)."</li>";
					}
				
				$dataset_count++;

				}
			}
				
	if($DEBUG2) echo "current size of series insertion array = ".sizeof($series_list).", should match $series_count<br />";
	
	$year_matrix_count = count($year_matrix);

//////////////////////////////////////
//current array structure
	if($DEBUG)
		array_print_2d($series_list, "Unique data Series in dataset=$datasets_id");
	if($DEBUG) 
		array_print_2d($year_matrix, "Original $legend_y versus $legend_x Matrix");
		
		
//////////////////////////////////////
//prepare arrays for plotting
	
//sort 2d inputs by year
	$inputs = array2d_sort($year_matrix, 'year');
	
//get a list of all the UNIQUE values at a particular key 
//in the sub-arrays
	$xvals = array();
	$xvals = array2d_unique1d($inputs, 'year');
	
	sort($xvals);

	array_print_1d($xvals, "X values");

//get the y-values for each series
//make a 2d array, with a unique value of 'title' as the 
//1d key, and a blank array as 2D key
//if there is more than one record in the input with the same 'title' it is ignored
	$yvals = array();
	
//this will create a new 2D array using a key from one of the second dimension array elements
	//$yvals = array2d_rekey_chart($year_matrix, 'title', 'title', 'value');
	$yvals = array2d_rekey_chart($year_matrix, 'title', 'value');
	
/*
	$yvals = array2d_unique2d2($year_matrix, 'title');
	
//now loop through the input array, grabbing each record, and assigning it 
//as a sub-array to the list of unique series titles
	foreach($year_matrix as $point_array) {
		foreach($yvals as $key => $value) {
			if($key == $point_array['title']) {
				////////////////////////$yvals[$key][$point_array['year']] = $point_array['value'];
				$yvals[$key][] = $point_array['value'];
				}
			}
		}
*/
	
//yvals should now be a multi-dmensional array, with the first dimension keys 
//the names of the series, and the sub-arrays a list of y-values for each series
	array_print_2d($yvals, "Y values");
	
//////////////////////////////////////
//plot current array
	plot_y_series($xvals, $yvals, $legend_x, $legend_y, $title, $title_short, "input");

//////////////////////////////////////
//our insertion sorted the new memory-based array by age. So, crawl through the array from the earliest year to the 
//latest. If we detect that a particular year doesn't exist, insert a new, empty array for that year
//insert new sub-arrays into the matrix, corresponding to missing years (extra X-axis points)
//access via for() loop
//$year_matrix - existing set of series data for a sepecific number of years
//$series_list - list of unique series, emptied records with no value or year set

	debug_text_print("<b>INSERT INTERPOLATED YEARS FOR EACH SERIES</b>");
	
/////////////////////////////////////	
//create a new array. When we have existing records for a particular year (> 1 means multiple series) 
//then use the values in $year_matrix. Otherwise, copy in the blank $series_list, changing values to the 
//missing year.
//our new 2D array
	$expanded_year_matrix = array();
		
	$first_year  = $year_matrix[0]['year'];
	$last_year   = $year_matrix[sizeof($year_matrix) - 1]['year'];
	$curr_year   = $first_year;
	$year_range  = $last_year - $first_year + 1;
	
	$i = 0; 
	$found_year_flag = false;
	
//for every year in the set
	while($curr_year <= $last_year) {
	
//grab all records for the current year from the original matrix, if present
		$found_year_flag = false;
		for($i = 0; $i < $year_matrix_count; $i++) {
			if($curr_year == $year_matrix[$i]['year']) {
				$expanded_year_matrix[] = $year_matrix[$i];
				$found_year_flag = true;
				}
			}
//if the current year wasn't found in the dataset, copy in a blank series. 	Add a new column at the end
		if(!$found_year_flag) {
			for($i = 0; $i < $series_count; $i++) {
				$expanded_year_matrix[] = $series_list[$i];
				$expanded_size = count($expanded_year_matrix);
				$expanded_year_matrix[$expanded_size - 1]['year'] = $curr_year;
				}
			}		
			
		$curr_year++;
		}
		
//unset the year_matrix
	$year_matrix = array();

	$expanded_year_matrix_count = count($expanded_year_matrix);

	$expected_size = $year_range * $series_count;
	
	if($DEBUG2) echo "expanded matrix (by year) is $expanded_size compared to expected $expected_size<br />";

//////////////////////////////////////
//prep the matrix for adding addtional points. We do this by scanning through te array, checking if 
//the 'primary' column is set to true. while it it, we keep writing the index we are at as the last
//real datapoint. When we hit a block of zeroes, we don't update the index, but keep writing this 
//value into the added blank year series points. We then reverse direction, adding the next valid 
//point. In the end, each array record has the previous and next 'real' point index listed.

	for($j = 0; $j < $series_count; $j++) {
	
		$curr_series_id = $series_list[$j]['series'];

		for($i = 0; $i < $expanded_year_matrix_count; $i++) {
			if($curr_series_id == $expanded_year_matrix[$i]['series']) {
				if($expanded_year_matrix[$i]['primary_value'] == true) {
					$last_valid = $i;
					}
			
				$expanded_year_matrix[$i]['last_valid_index'] = $last_valid;
				}
			}

		$rev_count = $expanded_year_matrix_count - 1;
	
		for($i = $rev_count; $i >= 0; $i--) {
			if($curr_series_id == $expanded_year_matrix[$i]['series']) {
				if($expanded_year_matrix[$i]['primary_value'] == true) {
					$next_valid = $i;
					}
				$expanded_year_matrix[$i]['next_valid_index'] = $next_valid;
				}
			}
		
		}

//now each record can find the last and previous values to create a local, interpolated 'y' value. 
	for($i = 0; $i < $expanded_year_matrix_count; $i++) {
		$point_array = $expanded_year_matrix[$i];
		if($point_array['primary_value'] == false) {
			
			$last_index = $point_array['last_valid_index'];
			$next_index = $point_array['next_valid_index'];
			
			$last_point_array = $expanded_year_matrix[$last_index];
			$next_point_array = $expanded_year_matrix[$next_index];
			
			$total_dist = $next_point_array['year'] - $last_point_array['year'];
			$last_dist  = $point_array['year'] - $last_point_array['year'];
			$next_dist  = $next_point_array['year'] - $point_array['year'];
			
			$last_value = $last_point_array['value'];
			$next_value = $next_point_array['value'];
			$value_diff = $next_value - $last_value;

//weighted average
			$last_ratio = $last_dist/$total_dist;
			$next_ratio = $next_dist/$total_dist;
			
			$interpolated_value = $last_value + ($value_diff * $last_ratio);
			
			////////////$expanded_year_matrix[$i]['value'] = number_format($interpolated_value, $decimals);
			$expanded_year_matrix[$i]['value'] = number_format($interpolated_value, $decimals, $dec_point, $thousands_sep);
			
			if($DEBUG2) echo "interpolate = last_value=$last_value, next_value=$next_value, last_year=".$last_point_array['year'].", year=".$point_array['year'].", next_year=".$next_point_array['year'].", last_dist=$last_dist, next_dist=$next_dist, last_ratio=$last_ratio,  next_ratio=$next_ratio, new_value = ".$expanded_year_matrix[$i]['value']."<br />";
			
			}
		}

/////////////////////////////////////
//output resulting year-padded matrix
		array_print_2d($expanded_year_matrix, "Expanded Year Matrix");
		
/////////////////////////////////////
//graph output

	
/////////////////////////////////////	
//copy the array to mysql table
//first, delete any records in input_interpolated table with the same dataset
	$query_string = "DELETE FROM input_curvefit WHERE datasets_id='".$datasets_id."'";
	$fetch_result = $db->query($query_string);
	
//create a mysql-fommat date-time
	$curr_time = date("Y-m-d H:i:s", time());

//now, write our records from the array into the table
	if($DEBUG) debug_text_print("Writing new table to database...");
	$write_count = 0;

	for($j = 0; $j < $series_count; $j++) {
	
		$curr_series_id = $series_list[$j]['series'];
		
		for($i = 0; $i < $expanded_year_matrix_count; $i++) {
			$point_array = $expanded_year_matrix[$i]; //get an individual record
			if($point_array['series'] == $curr_series_id) {
	
					$query_string = "INSERT INTO $db_tbl_curvefit 
					(year,age_series_id,value,date_created,date_modified,datasets_id,primary_value,interpolation_type) VALUES (".
					"'".$point_array['year']."',".
					"'".$point_array['series']."',".
					"'".$point_array['value']."',".
					"'".$curr_time."',".
					    "CURRENT_TIMESTAMP".",".
					"'".$datasets_id."',".
					"'".$point_array['primary_value']."',".
					"'".$type."')";
					
					if($DEBUG2) array_print_1d($point_array, "index=$j");
					
					$fetch_result = $db->query($query_string);
					
					if($fetch_result == 1)
						$write_count++;

					if($DEBUG2) echo "FETCH RESULT: $fetch_result<br />";

					}
				}
			}
			
			
//unset the series list
		$series_list = array();
			
//////////////////////////////////////
//prepare for array plot
//get a list of all the UNIQUE values at a particular key 
//in the sub-arrays
	$xvals = array();
	$xvals = array2d_unique1d($expanded_year_matrix, 'year');

	//COMMAS MAY BE WRECKING THE PLOT
	///////////////////////////////////////number_format($row['value'], $decimals, '.',''),

	sort($xvals);
	array_print_1d($xvals, "X values");

//get the y-values for each series
//return only one record of each value of 'title'
//make a 2d array, with each value of 'title' as the 
//1d key, and a blank array as 2D key
	$yvals = array();
	
	$yvals = array2d_rekey_chart($expanded_year_matrix, 'title', 'value');
	
//unset the expanded year matrix
	$expanded_year_matrix = array();

//////////////////////////////////////
//plot current array
	array_print_2d($yvals, "Y values");

//////////////////////////////////////
//plot current array	
	plot_y_series($xvals, $yvals, $legend_x, $legend_y, $title, $title_short, "input_expanded");

/////////////////////////////////////
//clean up		
	if($DEBUG) debug_text_print("Year-padding complete, $write_count records written to $db_tbl_curvefit");

	$_SESSION['stage']    = STAGE_CURVEFIT; //we are at stage login
	echo "</div>\n";
	include("footer.php");

?>