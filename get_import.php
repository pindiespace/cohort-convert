<?php
//import data
//session variables
	
//load the database class
require_once 'class.mysql.php';
require_once 'cohort_convert_library.php';
require_once 'excel_reader2.php';

//need to list separately
$db_tbl_accounts = "accounts";

session_start();

/////////////////////////////////////	
//titling
$page_title = "Import Excel Data";
include("header.php");
echo "<h2>Cohort Convert - Import Excel Data via Template</h2>\n";	
include("navigation.php");
echo "<!--description-->\n<div id=\"description\">\n";
//////////////////////////////////////
//write the debug level to output
if($DEBUG2 == true) 
	echo "<h3>DEBUG MODE - Level 2</h3>\n";
else if($DEBUG == true) 
	echo "<h3>DEBUG MODE</b> - Level 1</h3>\n";
	
echo "Results for importing data...";
echo "</div>\n";

//beginning of printed output
echo "<!--program output-->\n<div id=\"output\">\n";

//the table(s) we are using
/*
	$db_tbl_accounts    = 'accounts';
	$db_tbl_ageseries   = 'age_series';
	$db_tbl_datasets    = 'datasets';
	$db_tbl_input       = 'input';
*/
if(isset($_SESSION['ID'])) {
	
//////////////////////////////////////
//unique identifier for this spreadsheet
	define(MAX_DATAPOINTS, 100);                   //max datapoints, also max rows, cols in uploaded data

//template signature
	define(TEMPLATE_SIGNATURE, 'zackysquick');     //A8 in template
	define(TEMPLATE_SIGNATURE_ROW, 8);
	define(TEMPLATE_SIGNATURE_COL, 'A'); 
	
//age series information
	define(AGE_SERIES_TITLE_COL, 'A');             //A14 in template
	
	define(START_AGE_SERIES_ROW, 14);              //B14 in template
	define(START_AGE_SERIES_COL, 'B');
	define(END_AGE_SERIES_ROW, 14);                //B15 in template
	define(END_AGE_SERIES_COL, 'C');     

//year column information	
	define(START_YEAR_ROW, 9);                     //D9 in template
	define(START_YEAR_COL, 'D');
	
	define(LABEL_COL, 'A');                        //labels all in column A
	
//values
	define(START_VALUE_ROW, 14);
	define(START_VALUE_COL, 'D');
	
//all additional information
	define(INFO_COL, 'B');                         //information labeled in column A
	
	define(TITLE_ROW, 1);
	define(SHORT_TITLE_ROW, 2);
	define(NAME_ROW, 3);                           //name
	define(USERNAME_ROW, 4);                       //account name
	define(ORGANIZATION_ROW, 5);                   //organization
	define(EMAIL_ROW, 6);                          //email
	
/////////////////////////////////////	
//security checks
//http://us2.php.net/manual/en/book.filter.php
//http://us2.php.net/manual/en/filter.constants.php
//confirm data is present for a dataset
	$type        = filter_input(INPUT_POST, 'import_options', FILTER_SANITIZE_STRING);
	$decimals    = filter_input(INPUT_POST, 'decimals', FILTER_VALIDATE_INT);
	
/////////////////////////////////////
//get our uploaded file into the 'incoming' directory
	if(isset($_FILES)) {
	
//make sure the user provided a file
		if(empty($_FILES['input_file']['name'])) {
			debug_text_print("ERROR - no input file specified, exiting");
			include("footer.php");
			exit;
			}
			
		$file_name = $_FILES['input_file'. $i]['name'];
		$file_name = stripslashes($file_name);
		$file_name = str_replace("'","",$file_name);
		$copy = copy($_FILES['input_file']['tmp_name'], "incoming/".$file_name);
		
		if($DEBUG)
			debug_text_print("File is <b>$file_name</b><br />");
	
//check the file extension
		$info = pathinfo($file_name);
		if($info['extension'] != 'xls' && $info['extension'] != 'xlsx') {
			debug_text_print("ERROR - not an Excel file (".$info['extension'].") exiting");
			include("footer.php");
///////////////////		unset("incoming/".$file_name);
			exit;
			}
	
//create our Excel reader object, and load the incoming file
		$data = new Spreadsheet_Excel_Reader("incoming/".$file_name);
	
//delete the incoming file
//////////////////	unset("incoming/".$file_name);

////////////////////////////////////////////////////////////////////////////////////////
//dump to the screen
		echo $data->dump(true,true);

////////////////////////////////////////////////////////////////////////////////////////
//dump everything to database
//get the sheet as a 2D array, including row and column designators
		$out_sheet = $data->dump_array(false, false, 0, 'excel');	
		array_print_2d($out_sheet, "output from uploaded Excel file...");
				
//SHEET-SPECIFIC PROCESSING
//Make a record for our input table
//format for datasets table (always created when new excel file uploaded)
//ID                        int(11)
//author                    varchar(100)
//title                     varchar(250)
//title_short               varchar(20)
//legend_x                  varchar(100)
//legend_y                  varchar(100)
//date_created              datetime
//date_modified             timestamp
//accounts_id               (assigned when records are created, based on user login)

//look for the unique sig on the template spreadsheet
		$sheet_sig    = trim($data->val(TEMPLATE_SIGNATURE_ROW, TEMPLATE_SIGNATURE_COL));
		if($sheet_sig == TEMPLATE_SIGNATURE) {

//check to see if the account exists. If it does, allow upload. If it doesn't prompt 
//the user to create an account

//get the account name and other information
			$title        = trim($data->val(TITLE_ROW, INFO_COL));
			$short_title  = trim($data->val(SHORT_TITLE_ROW, INFO_COL));
			$name         = trim($data->val(NAME_ROW, INFO_COL));
			$username     = trim($data->val(USERNAME_ROW, INFO_COL));
			$organization = trim($data->val(ORGANIZATION_ROW, INFO_COL));
			$email        = trim($data->val(EMAIL_ROW, INFO_COL));

			if($DEBUG) 
				debug_text_print("Spreadsheet Data: name=$name, account_name=$username, organization=$organization, email=$email");

//confirm there is at least 1 data point to analyze
			$first_year  = $data->val(START_YEAR_ROW, START_YEAR_COL);
			$first_value = $data->val(START_VALUE_ROW, START_VALUE_COL);
			if(empty($first_year) || empty($first_value)) {
				echo debug_text_print("No data in this spreadsheet, exiting");
				include("footer.php");
				exit;
				}
				
//get the username from the database
			$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
			$db->connect_default();
			$db->query('SET storage_engine=MyISAM');
			
//get the current logged-in user
			$query_string = "SELECT * from $db_tbl_accounts WHERE ID='".$_SESSION['ID']."'";
			$fetch_result = $db->query($query_string);
			$row = $db->fetch_array($fetch_result);
		
			if(empty($row)) {
				echo "logged-in user not found in accounts";
				include("footer.php");
				exit;
				}
				
//store the current user's login name
			$curr_username = $row['username'];
			
//get the username listed in the spreadsheet
			$query_string = "SELECT * from $db_tbl_accounts WHERE username='".$username."'";
		
			if($DEBUG) 
				echo "query=$query_string<br />";

			$fetch_result = $db->query($query_string);
			$row = $db->fetch_array($fetch_result);
			
			if(empty($row)) {
				echo "username listed in spreadsheet not found in database";
				include("footer.php");
				exit;
				}
				
//we've matched the template, found data, and the username, so begin the analysis
//set accounts_id
		$accounts_id = $row['ID'];
		
		if($DEBUG)
			echo "account ID is $accounts_id <br />";		
			
//if username does not match current user, warning
			if($username != $curr_username) {
				echo debug_text_print("Username in spreadsheet ($username) does not match currently logged-in user ($curr_username)");
				include("footer.php");
				exit;
				}
				
		
//to get this far, username must match account username
						
//warn user if name, email are different in account compare to spreadsheet
		if($name != $row['name'])
			debug_text_print("Note - your account is valid, but the name attached to this account doesn't match the spreadsheet");
		if($email != $row['email'])
			debug_text_print("Note - your account is valid, but the email attached to this account doesn't math the spreadsheet");
				
				
/////////////////////////////////////////
//we are going to create a dataset - get the time
//create a mysql-fommat date-time
		$curr_time = date("Y-m-d H:i:s", time());
		
////////////////////////////////////////////////////////////////////////////////
//START PROCESSING
////////////////////////////////////////////////////////////////////////////////
//make sure we haven't inserted this record already
		$query_string = "SELECT * FROM $db_tbl_datasets WHERE author='$name' AND title='$title' AND title_short='$short_title' AND legend_x='Year' AND legend_y='Age Series' AND accounts_id='$accounts_id'";
			
		$fetch_result = $db->query($query_string);
		if(mysql_num_rows($fetch_result)) {
			if($DEBUG) 
				debug_text_print("tried to insert duplicate dataset record (no action taken)");
			include("footer.php");
			exit;
			}

/////////////////////////////////////////				
//create the datasets record - legends default to age series versus year
		$query_string = "INSERT INTO $db_tbl_datasets (author, title, title_short, legend_x, legend_y, date_created, date_modified, accounts_id) VALUES (
			'$name',
			'$title',
			'$short_title',
			'Year',
			'$short_title',
			'$curr_time', 
			'CURRENT_TIMESTAMP', 
			'$accounts_id') ";

		if($DEBUG) 
			echo "query_string for datasets is $query_string<br />";

//insert, and get the id of the just-inserted record
		$datasets_id = $db->insert_get_id($query_string); //program exits if fail
			
		if($DEBUG) 
			echo "datasets_id is $datasets_id<br />";

/////////////////////////////////////////
//begin pulling data from the spreadsheet
/////////////////////////////////////////	
//read the year information
			$year_matrix = array();
			$j = START_YEAR_COL;
			do {
				$temp = $data->val(START_YEAR_ROW, $j);
				if(!empty($temp)) {
					if(is_nan($temp)) { //catch cases where year is not a number
						debug_text_print("ERROR - one of your years is not a number (position =$j".START_YEAR_ROW." - fix before uploading");
						include("footer.php");
						exit;
						}
					else {
						$year_matrix[$j] = $temp;
						}
					}
				$j++;
				} while((!empty($temp)) && ($j < MAX_DATAPOINTS));
			
			array_print_1d($year_matrix, "YEARS");
		
//format of age series table (created if data specifies a new age series)
//ID int(11)
//age_start                 int(11)
//age_end                   int(11)
//title                     varchar(40)
//lifestage_id              (assigned when a new lifestage record needs to be created)
	
//read the age series information
			if($DEBUG)
				debug_text_print("Reading in age series data");
				
			$series_list = array();
			$i = START_AGE_SERIES_ROW;
			do {
//title of age series
				$modified = false; //flag for operation carried out
				$age_series_title = trim($data->val($i, AGE_SERIES_TITLE_COL));
				$age_series_title = strip_hiascii($age_series_title, '-');
				if($DEBUG) print "age series title is ".$age_series_title."<br />";
//starting and ending years in age series
				$ages   = array();
				$ages[] = trim($data->val($i, START_AGE_SERIES_COL));
				$ages[] = trim($data->val($i, END_AGE_SERIES_COL));
//look up age series in age series table
//if present, use its id. 
//If absent, add the new age series id to the table
				if($DEBUG) 
					print "ages[0] is ".$ages[0]." and ages[1] is ".$ages[1]." and ages[2] is ".$ages[2]."<br />";

				$query_string = "SELECT * FROM $db_tbl_ageseries WHERE age_start='".$ages[0]."' AND age_end='".$ages[1]."'";
				$fetch_result = $db->query($query_string);
				$row = $db->fetch_array($fetch_result);
				if(empty($row)) { //make a new age series entry
					debug_text_print("WE HAVE TO MAKE A NEW AGE SERIES ENTRY");
						
					if(!empty($ages[1]) && !empty($ages[0]) && ($ages[1] >= $ages[0])) { //we found a start age, and an end age
						$query_string = "INSERT INTO $db_tbl_ageseries (age_start, age_end, title, lifestage_id) VALUES ('".$ages[0]."','".$ages[1]."','".$age_series_title."','1000')";
						
						if($DEBUG) 
							print "query is $query_string<br />";
							
						if($fetch_result = $db->query($query_string)) {
							
							if($DEBUG) 
								print "inserted new age series<br />";
//insert, and return the ID for this record
							$query_string = "SELECT * from $db_tbl_ageseries WHERE title='".$age_series_title."' AND age_start='".$ages[0]."' AND age_end='".$ages[1]."'";
							$series_list[$row['title']][] = $db->insert_get_id($query_string); //program exits if fail
							$series_list[$row['title']][] = $ages[0];
							$series_list[$row['title']][] = $ages[1];

							$modified = true;
							}
						else {
							debug_text_print("ERROR - invalid age series (must be AGE_START-AGE_END) at position ".START_AGE_SERIES_COL."$i, exiting");
							include("footer.php");
							exit;
							} //couldn't insert a new age series into the database, ERROR
						} //if $ages[] array is not empty
					} //if there is no comparable age series in the database
				else { //use the existing age series entry
					$series_list[$row['title']][] = $row['ID'];
					$series_list[$row['title']][] = $row['age_start'];
					$series_list[$row['title']][] = $row['age_end'];
					$modified = true;
					} //end of found an age series
				$i++;
				} while($modified && (!empty($series_list)) && ($i < MAX_DATAPOINTS)); //max of 100 age series possible
				
				array_print_2d($series_list, "AGE SERIES LIST");
				
				
/////////////////////////////////////////////////////////////////////////////////
//format of inputs table (multiple records created)
//ID                        int(11)
//year                      int(11)
//age_series_id	            int(11)
//value	                    float
//date_created              datetime
//date_modified             timestamp
//datasets_id               (assigned when records are created)

	
//start writing to input table
//IMPORTANT - MySQL INSISTS on putting commas into large floating-point and decimal
//numbers
			$j = START_VALUE_ROW;
			do {
				$i = START_YEAR_COL;
				do {
					$modified         = false;
					$year             = trim($data->val(START_YEAR_ROW, $i));
					$value            = trim($data->val($j, $i));
					///preg_replace('#.*?([0-9]*(\.[0-9]*)?).*?#', '$1', $value);
					//$value            = strip_commas($data->val($j, $i));
					//$value            = trim($value);
					print "THE GODDAMMM VALUE IS $value<br />";
					$age_start        = trim($data->val($j, START_AGE_SERIES_COL));
					$age_end          = trim($data->val($j, END_AGE_SERIES_COL));
					$age_series_title = trim($data->val($j, AGE_SERIES_TITLE_COL));

//only process if not empty
					if(!empty($year) && !empty($value) && !empty($age_start) && !empty($age_end)) {
//recover an appropriate age series ID, using the age start and ange end (NOT the title of the age range)
						foreach($series_list as $age_range) {
							if($age_start == $age_range[1] && $age_end == $age_range[2]) {
								$age_series_id = $age_range[0];
								}
							}

//insert the record
						$query_string = "INSERT INTO $db_tbl_input (year, age_series_id, value, date_created, date_modified, datasets_id) VALUES( 
						'$year', 
						'$age_series_id', 
						'$value', 
						'$curr_time', 
						'CURRENT_TIMESTAMP', 
						'$datasets_id' 
						)";
						
					if($DEBUG) print "query string=$query_string<br />";
					
//try writing point to database
					if($DEBUG) print "writing..............<br />";
					$fetch_result = $db->query($query_string);
					$row = $db->fetch_array($fetch_result);
					if($DEBUG) print "fetch result was ".$fetch_result."<br />";
					if(!empty($row)) {
						$modified = true;
						}

					$modified = true;
					}
					
					$i++;
					$next_series_val = trim($data->val($j, $i));

					} while($modified && !empty($next_series_val) && ($i < MAX_DATAPOINTS));
					
				$j++;
				$next_series_age_start = trim($data->val($j, START_AGE_SERIES_COL));
				$next_series_age_end   = trim($data->val($j, END_AGE_SERIES_COL));
				$next_series_val       = trim($data->val($j, START_VALUE_COL));
				
				if(!empty($next_series_val) && !empty($next_series_age_start) && !empty($next_series_age_end)) {
					debug_text_print("Beginning next series:</b> $age_series_title ($next_series_age_start - $next_series_age_end)");
					}
				
				} while(!empty($next_series_val) && !empty($next_series_age_start) && !empty($next_series_age_end) && ($j < MAX_DATAPOINTS));

					

////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////					



			} //end of excel template has correct signature
		
		else {
			debug_text_print("Your Excel does not match the template, download a new one"); //didn't find correct sig in A8
			} 
				
		}//end of template match 

	else {
		debug_text_print('File was not uploaded, $_FILES array not set');
		} //end of $_FILES array not set

/////////////////////////////////////
//clean up		

//delete the uploaded excel file
	///////////////////////////////////unlink("incoming/".$file_name);

	if($DEBUG) 
		debug_text_print("Data loaded into a new dataset");
//no change in computation state
	echo "</div>\n";
		} //end of $_SESSION was set
		
	include("footer.php");
	

?>