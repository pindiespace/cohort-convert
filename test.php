<?php


require_once 'class.mysql.php';

echo '<h1>TEST!</h1>';
echo '<ul>';

/* Create the new db connection. */

  $db = new DB();
  $db->connect('localhost', 'root', 'root', false, false, 'examples', 'tbl_');
  echo '<li> Connect to the database succesfully </li>';
  echo '<li> DB Charset: '.$db->valid_charset.' </li>';


/* Create a new table */

  // Set mysql storage engine.
  $db->query('SET storage_engine=MyISAM');
  // Checks exist table and if not exist, create a new table.
  if ($db->table_exists('example')) {
    //Table: 'example'
    $db->query("CREATE TABLE ".$db->tbl_pre."example (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(50) NOT NULL,
      `email` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ");
    echo '<li> Table `example`, created succesfully! </li>';
  }


/* Insert a new record */

  $db->query_insert('example', array('name' => 'taylan', 'email' => 'taylan@mail.com'));
  echo '<li> A new record inserted succesfully! </li>';


/* Optimize the table */

  $db->optimize_table('settings');
  echo '<li> Table `example`, optimized succesfully! </li>';


/* Fetch a result row */

  $fetch_result = $db->query("SELECT * FROM ".$db->tbl_pre."example");
  echo '<strong>Fetch Array</strong>: <br /> ';
  if ($fetch_result) {
    while ($row = $db->fetch_array($fetch_result)) {
      echo 'ID: '.$row['id'].'<br />';
      echo 'Name: '.$row['name'].'<br />';
      echo 'Email: '.$row['email'].'<br />';
    }
    echo '<i>Num Rows: '.$db->num_rows.' </i>';
  }
  echo '<li> Table `example`, fetched succesfully! </li>';


/* Fetch all rows */

  $fetch_all_result = $db->query("SELECT * FROM ".$db->tbl_pre."example WHERE id = 1");
  echo '<strong>Fetch All Array</strong>: <br /> ';
  if ($result) {
    foreach ($db->fetch_all_array($fetch_all_result) as $key => $val) {
      echo 'ID: '.$val['id'].'<br />';
      echo 'Name: '.$val['name'].'<br />';
      echo 'Email: '.$val['email'].'<br />';
    }
  } else {
    echo 'Records not found that match your request! <br />';
  }
  echo '<li> Table `example`, fetched all succesfully! </li>';


/* First row */

  $frow = $db->query_first("SELECT * FROM ".$db->tbl_pre."example");
  echo '<strong>First Row</strong>: <br /> ';
  if (!empty($frow)) {
    echo 'ID: '.$frow['id'].'<br />';
    echo 'Name: '.$frow['name'].'<br />';
    echo 'Email: '.$frow['email'].'<br />';
  }else {
    echo 'Records not found that match your request! <br />';
  }
  echo '<li> Table `example`, selected first row succesfully! </li>';


/* UPDATE */

  $db->query_update('example', array('name' => 'aktepe', 'email' => 'aktepe@mail.com'), 'id = 1');
  echo '<li> Registration number 1 has been updated succesfully </li>';


/* DELETE */

  $db->query_delete('example', 'name = \'taylan\'');
  echo '<li> Records have been deleted which name is `taylan` succesfully! </li>';
  
echo '<h4>To see the full results, please refresh the page.</h4>'

?>
