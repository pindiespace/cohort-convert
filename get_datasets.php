<?php
//get a list of existing datasets, along with info to use one in calculations
//open the database

//load the database class
require_once 'class.mysql.php';

//table names set in header.php
//the above defaults were set - change with $db->init_info($host, $user, $pwd, $db_name, $db_tbl);
	$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
	$db->connect_default();

	$db->query('SET storage_engine=MyISAM');
//echo '<i>Num Rows: '.$db->num_rows.' </i>';
	
	$query_string = "";
	$admin = false;

//find out if the user (as listed in the session variable) is an admin
	if(isset($_SESSION['ID'])) {
		
//calculate which datasets to show based on their availability at this level
//construct a query string showing only those datasets which can have 
//calculations done at this level
	
		$query_string = "SELECT * FROM  $db_tbl_datasets WHERE ID='".$_SESSION['ID']."'";
		$fetch_result = $db->query($query_string);
		if(mysql_num_rows($fetch_result)) {
//there can only be one dataset record
			$row = $db->fetch_array($fetch_result);
			if(isset($row))
				$admin = $row['admin'];
			}
		

		}

//get all the records
	$query_string = "SELECT * FROM ".$db_tbl_datasets;
	$fetch_result = $db->query($query_string);
	if($fetch_result) {
		while ($row = $db->fetch_array($fetch_result)) {
//process into html option controls
//don't show if the user doesn't own the dataset
			if(isset($_SESSION['ID']) || $admin) {
				if($_SESSION['dataset_id'])
					echo "<option checked=\"checked\" value=\"".$row['ID']."\">".$row['title']."</option>\n";
				else
					echo "<option value=\"".$row['ID']."\">".$row['title']."</option>\n";
				}
			}
		}
		
//this is for debugging
		if(!isset($_SESSION['ID']))
			echo "<option value=\"NOT LOGGED IN\">NOT LOGGED IN</option>\n";


?>