<? 

///////////////////////////////////////////////////////////////////////////////
//class includes
/////////////////////////////////////////////////include('graph2.php');
require_once 'graph2.php';
require_once 'class.excelwriter.php';

///////////////////////////////////////////////////////////////////////////////
//class constants

///////////////////////////////////////////////////////////////////////////////
//internal variables
//sorting key
	$arr_sort_key = "";

///////////////////////////////////////////////////////////////////////////////
//general functions
///////////////////////////////////////////////////////////////////////////////
//security check - preg match against an input field
function check_input_text($text) {
	if(preg_match("/^[0-9]+:[X-Z]+$/D", $text))
 		return true;
	else
 		return false;
	}
	
////////////////////////////////////////////////////////////////////////////////
//strip ascii chars > 128 that may not display in web browsers
//replace with equivalents when possible (e.g. long hyphen replaced with ascii hyphen)
//otherwise, put in substitute character
function strip_hiascii($text, $substitute) {
	$temp = str_split($text);
	for($i = 0; $i < count($temp); $i++) {
		$ascii = ord($temp[$i]);
		if($ascii > 127) {
			switch($ascii) {
				case 228:
					$temp[$i] = '-';
					break;
				default:
					$temp[$i] = $substitute;
					break;
				}
			}
		}
	$temp = implode("", $temp);
	return $temp;
	}
	
	
/////////////////////////////////////////////////////////////////////////////////
//strip commas from inputted numbers so they are properly handled by php
function strip_comma($string){
	//return preg_replace('#.*?([0-9]*(\.[0-9]*)?).*?#', '$1', $string);
	//return str_replace(',', '', $string);
	 $v1 = explode (',', $string) ;
        $string = "" ;
        foreach($v1 as $t) {
			if($t != ',' && ord($t) < 128)
            	$string .= $t;
			}
	return $string;
	}  

	
/////////////////////////////////////////////////////////////////////////////////
//generated random text
function rand_text($text_length) {

	while($newcode_length < $text_length) {
		$x=1; $y=3;
		$part = rand($x,$y);
		//if($part==1){$a=48;$b=57;}  // Numbers
		//if($part==2){$a=65;$b=90;}  // UpperCase
		//if($part==3){$a=97;$b=122;} // LowerCase
		$a=97; $b=122;
		$code_part=chr(rand($a,$b));
		$newcode_length = $newcode_length + 1;
		$newcode = $newcode.$code_part;
		}
	return $newcode;
	}
	

///////////////////////////////////////////////////////////////////////////////
//CHARTING FUNCTIONS
///////////////////////////////////////////////////////////////////////////////
//print text for debugging
function debug_text_print($text) {
	echo "<div class=\"raw_output\">\n$text</div>\n";
	}

////////////////////////////////////////////////////////////////////////////////
//plot a series, 2d array
//'year', 'series', 'value'
//$input_array - dataset
function plot_y_series(array $xvals, array $yvals, $legend_x, $legend_y, $title, $title_short, $file_name) {

	$chart = new graph(1000,480);
	$chart->parameter['path_to_fonts'] = 'fonts/'; // path to where fonts are stored
	$chart->parameter['title']         = $title;
	$chart->parameter['x_label']       = $legend_x;
	$chart->parameter['y_label_left']  = $legend_y;
	
//write to file, rather than direct output
	$chart->parameter['file_name']     = $file_name; //NEED TO MAKE NAME=USER-FILE_NAME
	$chart->parameter['output_format'] = "PNG";

/*
	for($i = 0; i < $xvals; $xvals++) {
		$val = $xvals[$i];
		$xvals[$i] = number_format($val, 2, '.','');
		}
	for($i = 0; i < $yvals; $yvals++) {
		$val = $yvals[$i];
		$yvals[$i] = number_format($val, 2, '.','');
		}
*/

//assign x values 
	$chart->x_data = $xvals;
		
	$order = array();
	
//quick and dirty way to change color of lines
	$colorset = array('black', 'maroon', 'green', 'purple', 'red', 'lime', 'blue');
	$i = 0;
		
	foreach($yvals as $key => $new_series) {
		$chart->y_data[$key] = $new_series;
		$chart->y_order[]    = $key;
//		$chart->y_format[$key] = array('colour' => 'blue', 'point' => 'diamond', 'point_size' => 12, 'line' => 'brush', 'legend' => $key);
		$chart->y_format[$key] = array('colour' => $colorset[$i++], 'point' => 'diamond', 'point_size' => 12, 'line' => 'brush', 'legend' => $key);
		}
		
	$chart->parameter['legend']             = 'top-left';
	$chart->parameter['legend_border']      = 'black';
	$chart->parameter['legend_offset']      = 4;

	$chart->parameter['y_resolution_left']  = 0;
	$chart->parameter['y_resolution_right'] = 0;

	$chart->parameter['point_size']         = 6;
	$chart->parameter['x_axis_angle']       = 60; // x_axis text rotation
	$chart->parameter['y_decimal_left']     = 2;
	$chart->parameter['y_axis_num_ticks']   = 6;

	$chart->parameter['brush_type']         = 'circle';
	$chart->parameter['brush_size']         = 1;
	$chart->parameter['shadow_offset']      = 4;
	
	$chart->draw();
	
//display the chart as an image
	echo "<hr /><div>\n<img src=\"".$chart->parameter['path_to_images'].$chart->parameter['file_name'].".".strtolower($chart->parameter['output_format'])."\" />\n</div>\n";

	}
	
	
///////////////////////////////////////////////////////////////////////////////
//array functions
///////////////////////////////////////////////////////////////////////////////
 //insert an array at a specific position in an array
 //http://www.justin-cook.com/wp/2006/08/02/php-insert-into-an-array-at-a-specific-position/
 function array_insert(&$array, $insert, $position){
	if(!is_numeric($position)) return false;
		if(is_object($insert) OR is_array($insert)) {
			$array  = array_merge($array, array($insert));
			}
		else{
			$head   = array_slice($array, 0, $position);
			$insert = array($insert);
			$tail   = array_slice($array, $position);
			$array  = array_merge($head, $insert, $tail);
			}

	return true;
	}
	
	
///////////////////////////////////////////////////////////////////////////////
//print a one-dimensional array's contents
function array_print_1d($arr, $arr_title) {

	$arr_count = count($arr);
	
	if(is_array($arr) && $arr_count > 0)  {

		$i = 0;
		echo "<hr /><b>1-D Array: $arr_title Length: ".$arr_count."</b>\n<div class=\"raw_table\">\n";	
		echo "<table>\n";
		echo "<tr>\n";
//array indices
		foreach($arr as $key => $value) {
			echo "<th>$key</th>";
			}
			echo "</tr>\n<tr>";
//values
		foreach($arr as $key => $value) {
			echo "<td>".$value."</td>";
			}
		echo "</tr>\n</table>\n</div>\n";
		return 1;
		}
		
	debug_text_print("passed value not an array for array2d_sort()");
	return 0;
	}
		

///////////////////////////////////////////////////////////////////////////////
//print an 2-dimensional array's contents
function array_print_2d($arr, $arr_title) {

	$arr_count = count($arr);
	
	if(is_array($arr) && $arr_count > 0)  {
	
		$i = 0;
		echo "<hr /><b>2D TITLE:$arr_title LENGTH:$arr_count</b>\n<div class=\"raw_table\">\n";
		echo "<table>\n";
		foreach($arr as $key => $series_arr) {
			echo "<tr>\n";
//the first time, write a header, and the array index
			if($i == 0) {
				echo "<th>Index</th>";
				echo "<th>key</th>";
				foreach($series_arr as $key2 => $value) {
					echo "<th>$key2</th>";
					}
				echo "</tr>\n<tr>\n";
				}
//always write the values
			echo "<td>$i</td>";
			echo "<td>$key</td>";
			foreach($series_arr as $key2 => $value) {
				echo "<td>".$value."</td>";
				}
			echo "</tr>\n";	
			$i++;
			}
		echo "</table>\n</div>\n";
		return 1;
		}
		
	debug_text_print("passed value not an array for array2d_sort()");
	return 0;
	}


///////////////////////////////////////////////////////////////////////////////
//return a 1-dimensional array of all the unique values within the sub-arrays
//which are part of a 2D array
//$arr['a']['val'] = 2;
//$arr['b']['val'] = 3;
//$arr['c']['val'] = 2;
//$return_arr = 2, 3
function array2d_unique1d($arr, $unique_key) {

	if(is_array($arr) && count($arr) > 0)  {

		$return_arr = array();
		
		foreach($arr as $sub_arr) {
			$found_flag = false;
			foreach($return_arr as $value) {
				if($sub_arr[$unique_key] == $value) 
				$found_flag = true;
				}
			if(!$found_flag)
				$return_arr[] = $sub_arr[$unique_key];
			}
		
		return $return_arr;
		}
	
	debug_text_print("passed value not an array for array2d_sort()");
	return 0;
	}
	

////////////////////////////////////////////////////////////////////////////////
//return a 2-dimensional array of a subset of the sub-arrays in a 2D array
//The first dmension key is the set of unique values under the original array $unique_key index
//The second dimension is the sub-array;
//searching on 'name'
//$arr['a'] = array('first', 'bob');
//$arr['b'] = array('secnd', 'phil');
//$arr['c'] = array('third', 'bob');
//$return_arr['bob'] = array('first', 'bob');
//$return_arr['phil'] = array('secnd', 'bob');
function array2d_unique2d($arr, $unique_key) {

	if(is_array($arr) && count($arr) > 0)  {

		$return_arr = array();
		
		foreach($arr as $sub_arr) {
			$found_flag = false;
			foreach($return_arr as $value) {
				if($sub_arr[$unique_key] == $value) 
					$found_flag = true;
				}
			if(!$found_flag)
				$return_arr[$sub_arr[$unique_key]] = $sub_arr;
			}
		
		return $return_arr;	
		}
	
	debug_text_print("passed value not an array for array2d_sort()");
	return 0;
	}
	
////////////////////////////////////////////////////////////////////////////////
//return a 2-dimensional array of a subset of sub-arrays in a 2D array
//make the array as $return_arr[values at unique key][unique_key];  
//use this to force a 2D array, with empty arrays in the second 
//dimension, and the first dimension keys corresponding to unique values 
//at the specified 'unique_key' in the original array
function array2d_unique2d2($arr, $unique_key) {

	if(is_array($arr) && count($arr) > 0)  {
		$return_arr = array();
		
		foreach($arr as $sub_arr) {
			$found_flag = false;
			foreach($return_arr as $value) {
				if($sub_arr[$unique_key] == $value) 
					$found_flag = true;
				}
			if(!$found_flag) {
				$return_arr[$sub_arr[$unique_key]] = array();
				}
			}
		
		return $return_arr;
		}
		
	debug_text_print("passed value not an array for array2d_sort()");		
	return 0;
	}
	
////////////////////////////////////////////////////////////////////////////////
//take a 2D array, and re-key the reference to its 2nd dimension, using a 
//value found in the 2nd dimension using $val_key
//we are assuming that we have MULTIPLE records in the input array listed under 
//new_key, and we want to make sub-arrays, with new_key as their 1d key, and 
//individual values at $val_key making up the elements of the sub-arrays. In 
//effect, we are eliminating some of the the input sub-arrays, and tacking a value 
//from them into a new array, keyed with another value from the input array
//COMMON USE:
//input data may have individual records with values under a particular y data 
//series. This function will find all the unique series in the input records, 
//make a new array keyed with the series names, ids, or titles in the 1d, and 
//put the individual values for each series as its new sub-array. In addition
//the sub-array is key-indexed, so each value in the second dimension can be 
//accessed by a second key present in the original array (e.g. first dimension 
//as series name, second dimension as year)
function array2d_rekey($arr, $new_key, $val_key_key, $val_key) {

	if(is_array($arr) && count($arr) > 0)  {

//get the y-values for each series
//return only one record of each value of 'title'
//make a 2d array, with each value of 'title' as the 
//1d key, and a blank array as 2D key
		$return_value = array();
		$return_value = array2d_unique2d2($arr, $new_key);
	
//now loop through the input array, grabbing each record, and assigning it 
//as a sub-array to the list of unique titles
		foreach($arr as $point_array) {
			foreach($return_value as $key => $value) {
				if($key == $point_array[$new_key]) {
					$return_value[$key][$point_array[$val_key_key]] = $point_array[$val_key];
					}
				}
			}
	
		return $return_value;
		}
	
	debug_text_print("passed value not an array for array2d_sort()");
	return 0;
	}
	
	
////////////////////////////////////////////////////////////////////////////////
//this function will find all the unique series in the input records, make 
//a new array keyed with the series names, ids, or titles, in the 1d, and 
//put individual values associated with each unique series. The difference from 
//array2d_rekey() is that the values in the second dimension DO NOT have 
//keys of their own - only numerical access is possible. This is ESSENTIAL 
//for the chart module to work with the data
//function array2d_rekey_chart($arr, $new_key, $val_key_key, $val_key) {
function array2d_rekey_chart($arr, $new_key, $val_key) {

	if(is_array($arr) && count($arr) > 0)  {

//get the y-values for each series
//return only one record of each value of 'title'
//make a 2d array, with each value of 'title' as the 
//1d key, and a blank array as 2D key
		$return_value = array();
		$return_value = array2d_unique2d2($arr, $new_key);
	
//now loop through the input array, grabbing each record, and assigning it 
//as a sub-array to the list of unique titles
		foreach($arr as $point_array) {
			foreach($return_value as $key => $value) {
				if($key == $point_array[$new_key]) {
					$return_value[$key][] = $point_array[$val_key];
					}
				}
			}
	
		return $return_value;
		}
		
	debug_text_print("passed value not an array for array2d_sort()");
	return 0;
	}	
	

/////////////////////////////////////////////////////////////////////////////////
//compare function - 2D array sort
//NOTE - use of global variable is necessary!
function cmpkey($a, $b) {
	global $arr_sort_key;
   	 return strcmp($a[$arr_sort_key], $b[$arr_sort_key]);
	}

////////////////////////////////////////////////////////////////////////////////
//sort a 2D array by a sort key present in the second dimension
//uses local function for 2D sort
function array2d_sort($arr, $sort_key) {
	global $arr_sort_key;
	
	if(is_array($arr) && count($arr)>0)  {
		$arr_sort_key = $sort_key; 
		usort($arr, "cmpkey");
		return $arr;
		}
		
	debug_text_print("passed value not an array for array2d_sort()");
	return 0;
	}
	
	
/////////////////////////////////////////////////////////////////////////////////
//alternative array sort
   function sort2d($array, $index, $order='asc', $natsort=FALSE, $case_sensitive=FALSE)  
    { 
        if(is_array($array) && count($array)>0)  
        { 
           foreach(array_keys($array) as $key)  
               $temp[$key]=$array[$key][$index]; 
               if(!$natsort)  
                   ($order=='asc')? asort($temp) : arsort($temp); 
              else  
              { 
                 ($case_sensitive)? natsort($temp) : natcasesort($temp); 
                 if($order!='asc')  
                     $temp=array_reverse($temp,TRUE); 
           } 
           foreach(array_keys($temp) as $key)  
               (is_numeric($key))? $sorted[]=$array[$key] : $sorted[$key]=$array[$key]; 
           return $sorted; 
      } 
      return $array; 
    }  
	
	
/////////////////////////////////////////////////////////////////////////////////
//check a 1d array for high and low values
function array_high_low_1d($arr) {

	if(is_array($arr) && count($arr) > 0)  {

		$return_arr = array();
		$return_arr['high'] = 0;
		foreach($arr as $value) {
			if($value > $high_num)
				$return_arr['high'] = $value;
			}
		
		$return_arr['low'] = $return_arr['high'];
		foreach($arr as $value) {
		if($value < $return_arr['low']) 
			$return_arr['low'] = $value;
			}
		
		return $return_arr;
		}
	
	debug_text_print("passed value not an array for array2d_sort()");
	return 0;
	}
	

///////////////////////////////////////////////////////////////////////////////
//database functions

	
///////////////////////////////////////////////////////////////////////////////
//export functions
//write an excel file
function write_xls($column_values, $output_titles, $db_table_name, $dataset_name, $legend_x, $legend_y, $account, $organization, $output_mode) {
	
	$excel_fname = "$account-$db_table_name_excel.xls";
	$excel_path = "output/excel_fname";

	$excel = new ExcelWriter($excel_path);
	if($excel == false) {
		echo $excel->error;
		return;
		}
		
//write information about the data
	$excel->writeRow();
	$excel->writeTitleCol("Series Data: $dataset_name");
	$excel->writeRow();
	$excel->writeCol("<b>Table:</b> $db_table_name");
	$excel->writeRow();
	$excel->writeCol("<b>Account:</b> $account");
	$excel->writeRow();
	$excel->writeCol("<b>Organization:</b> $organization");
	$excel->writeRow();
	$excel->writeCol("<b>Rows:</b> $legend_x");
	$excel->writeRow();
	$excel->writeCol("<b>Columns:</b> $legend_y");
	$excel->writeRow();
//write the column titles
		$excel->writeTitleLine($output_titles);
					
	$title_flag   = true;
	$column_names = array();
	
//write individual columns of data				
	foreach($column_values as $key => $row) {
		//array_unshift($row, $key);
		if($title_flag) {
			$column_names = array_keys($row);
			array_unshift($column_names, "Series");
			$excel->writeTitleLine($column_names);
			$title_flag = false;
			}
		array_unshift($row, $key);
		$excel->writeLine($row);
		$count++;
		}
	
//finish output	
	$excel->close();
	
//link to the file just created, or download
//    'file' = save to file, provide hyperlink
//'download' = immediate user download prompt
	switch($output_mode) {
		case 'file':
			echo "<hr />\n<form method=\"post\" action=\"force_download.php\">\n";
			echo "<input type=\"hidden\" name=\"file\" id=\"file\" value=\"$excel_path\" />\n";
			echo "<input type=\"submit\" name=\"sub\" value=\"Click to download $excel_fname\" />\n";
			echo "</form>\n";
			break;
		case 'download':
//now send the xls file as output back to the user (download)
			header("Content-Disposition: attachment; filename=\"" . $excel_path . "\"");
			header("Content-Length: " . filesize($excel_path));
			header("Content-Type: application/vnd.ms-excel");
			readfile($excel_path);
			break;
		default:
			break;
		}


	}


///////////////////////////////////////////////////////////////////////////////
//general math functions
//adapted from - http://www.ajdesigner.com/php_code_statistics/mean.php
///////////////////////////////////////////////////////////////////////////////


//1. given two numbers along the x axis (run) and two numbers along the y axis (rise)
//return a weighted average of the y value, adjusted for its position along the 
//x run
// starty           midy???             endy
//
// startx           midx                endx
//2. assume each record in the 2D array has a field 'last_valid_index' and 
//'next_valid_index' which provide the indices of the stating and ending xy points
// we are interpolating our y value from. also, 
//3. the x axis value is listed under 'year'
function interpolate_xy_linear($startx, $starty, $endx, $endy, $midx) {

	$total_x_dist  = $endx - $startx;
	$last_x_dist   = $midx - $startx;
	$total_y_dist  = $endy - $starty;
	
	if($total_x_dist != 0) {
		$last_ratio = $last_x_dist/$total_x_dist;
		}
	else {
		$last_ratio = 0;
		}
	
	return $starty + ($total_y_dist * $last_ratio);
	
	}

///////////////////////////////////////////////////////////////////////////////
//input:  array of numbers
//output: average 
function mean ($a) {
  //variable and initializations
  $the_result = 0.0;
  $the_array_sum = array_sum($a); //sum the elements
  $number_of_elements = count($a); //count the number of elements

  //calculate the mean
  $the_result = $the_array_sum / $number_of_elements;

  //return the value
  return $the_result;
}



///////////////////////////////////////////////////////////////////////////////
//input:  array of numbers
//output: median
function median ($a) {
  //variable and initializations
  $the_median = 0.0;
  $index_1 = 0;
  $index_2 = 0;

  //sort the array
  sort($a);

  //count the number of elements
  $number_of_elements = count($a); 

  //determine if odd or even
  $odd = $number_of_elements % 2;

  //odd take the middle number
  if ($odd == 1)
  {
    //determine the middle
    $the_index_1 = $number_of_elements / 2;

    //cast to integer
    settype($the_index_1, "integer");

    //calculate the median 
    $the_median = $a[$the_index_1];
  }
  else
  {
    //determine the two middle numbers
    $the_index_1 = $number_of_elements / 2;
    $the_index_2 = $the_index_1 - 1;

    //calculate the median 
    $the_median = ($a[$the_index_1] + $a[$the_index_2]) / 2;
  }

  return $the_median;
}

///////////////////////////////////////////////////////////////////////////////
//input:  array of numbers
//output: standard deviation
//standard_deviation_population returns the standard deviation given the population.
function standard_deviation_population ($a, $the_mean) {
//variable and initializations
  $the_standard_deviation = 0.0;
  $the_variance = 0.0;
// $the_mean = 0.0;
//$the_array_sum = array_sum($a); //sum the elements
  $number_elements = count($a); //count the number of elements

//calculate the mean
// $the_mean = $the_array_sum / $number_elements;
	if($number_elements != 0) {
//calculate the variance
	  for ($i = 0; $i < $number_elements; $i++) {
    //sum the array
  	  $the_variance = $the_variance + ($a[$i] - $the_mean) * ($a[$i] - $the_mean);
		}

		$the_variance = $the_variance / $number_elements;

//calculate the standard deviation
		$the_standard_deviation = pow( $the_variance, 0.5);
  
		}

//return the variance
	return $the_standard_deviation;
	}

///////////////////////////////////////////////////////////////////////////////
//variance
//variance_population returns the variance given the entire population.
//input: array of numbers
//output: variance
function variance_population ($a) {
  //variable and initializations
  $the_variance = 0.0;
  $the_mean = 0.0;
  $the_array_sum = array_sum($a); //sum the elements
  $number_elements = count($a); //count the number of elements

  //calculate the mean
  $the_mean = $the_array_sum / $number_elements;

  //calculate the variance
  for ($i = 0; $i < $number_elements; $i++)
  {
    //sum the array
    $the_variance = $the_variance + ($a[$i] - $the_mean) * ($a[$i] - $the_mean);
  }

  $the_variance = $the_variance / $number_elements;

  //return the variance
  return $the_variance;
}



//binomial_coefficient returns the binomial coefficient given n, possibilities, and k, unordered outcomes.

function binomial_coefficient ($n, $k) {
  //variable and initializations
  $the_result = 0;
  $n_factorial = 0;
  $k_factorial = 0;
  $n_k_factorial = 0;

  //calculate n,k n-k factorial
  $n_factorial = factorial_integer($n);
  $k_factorial = factorial_integer($k);
  $n_k_factorial = factorial_integer($n - $k);

  if ($n_factorial != "error" and 
      $k_factorial != "error" and
      $n_k_factorial != "error")
  {
    $the_result = $n_factorial / ($k_factorial * $n_k_factorial);
  }
  else
  {
    return "error";
  }

  return $the_result;
}

function factorial_integer ($k)
{
  //variable and initializations
  $the_result = 1;

  //check to see if k is an integer
  if (!is_int($k))
  {
    return "error";
  }

  //check for k < 0
  if ($k < 0)
  {
    return "error";
  }

  //0! = 1
  if ($k == 0)
  {
    return 1;
  }

  //calculate the result
  for ($i = 2; $i <= $k; $i++)
  {
    $the_result = $the_result * $i;
  }

  //return the value
  return $the_result;
}
?>