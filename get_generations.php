<?php
//get a list of existing generations, along with info to use one in calculations
//open the database

//load the database class
require_once 'class.mysql.php';

//the table(s) we are using
	$db_tbl   = 'generations_model';

//the above defaults were set - change with $db->init_info($host, $user, $pwd, $db_name, $db_tbl);
	$db = new DB();
    
 // $db->connect($host, $user, $pwd, false, false, $db_name, '');
	$db->connect_default();
//  echo '<li> Connect to the database succesfully </li>';
//  echo '<li> DB Charset: '.$db->valid_charset.' </li>';
  
	$db->query('SET storage_engine=MyISAM');
//echo '<i>Num Rows: '.$db->num_rows.' </i>';

//get all the records
	$fetch_result = $db->query("SELECT * FROM ".$db->tbl_pre.$db_tbl);
	if($fetch_result) {
		while ($row = $db->fetch_array($fetch_result)) {
//process into html option controls
			echo "<option value=\"".$row['ID']."\">".$row['title']."</option>\n";
			}
		}


?>