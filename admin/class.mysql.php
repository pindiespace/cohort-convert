<?php

/////////////////////////////////////////////////////////////////////////////////
//MySQL Database Class
// modified from template distributed under GNU General Public License by 
//Taylan Aktepe (http://www.taylanaktepe.com)
/////////////////////////////////////////////////////////////////////////////////
 
  class DB {
  
//////////////////////////////////////////////////////////////////////////////////
//variables
//////////////////////////////////////////////////////////////////////////////////
		var $link_id       = 0;       // resource of the database link identifier
		var $query_id      = 0;       //resource of the query
		var $record        = array(); //array of the rows
		var $valid_charset = '';      //valid MySQL character set
		var $num_rows      = 0;       //rowcount
      //var $insert_id     = 0;       // The last performed query
	  
	  
//set defaults for access
		private static $db_current_host     = 'localhost';
		private static $db_current_username = 'default_user';
		private static $db_current_password = 'changeme';
		private static $db_current_db       = 'cohort_convert'; // name of database in MySQL

//////////////////////////////////////////////////////////////////////////////////
//constructor
//////////////////////////////////////////////////////////////////////////////////
	function DB() {
	
		}

		
//////////////////////////////////////////////////////////////////////////////////
//initialize our connection, if defaults above aren't desired
//////////////////////////////////////////////////////////////////////////////////
	public function init_info($db_host, $db_username, $db_password, $db_db_name) {
		if(empty($db_host) || empty($db_username) || empty($db_password) || empty($db_db_name)) {
			return false;
			}
		else {
			DB::$db_current_host     = $db_host;
			DB::$db_current_username = $db_username;
			DB::$db_current_password = $db_password;
			DB::$db_current_db       = $db_db;
			return true;
			}
	
		}
		

//////////////////////////////////////////////////////////////////////////////////
//mysql_connect();
//Connect and select database.
//@param string     The database host. (default 'localhost')
//@param string     The database username. (default 'root')
//@param string     The database user password. (default '')
//@param boolean    true if persistent connection, false if not. (default false)
//@param string     The database name.
//@return string    The database table prefix, if present  
///////////////////////////////////////////////////////////////////////////////////
//@return boolean true if new connection, false if not. (default false)
///////////////////////////////////////////////////////////////////////////////////
      public function connect($db_host = 'localhost', $db_username = 'root', $db_password = '', $new_link_id = false, $pconnect = false, $db_name, $table_prefix = '_') {
        // Construct the username and tables prefix.		
        $this->db_username = $db_username;
        $this->db_name = $db_name;
        $this->tbl_pre = $table_prefix;
        // Connect to the database.
        if ($pconnect) {
          $this->link_id = @mysql_pconnect($db_host, $db_username, $db_password);
        } else {
          $this->link_id = @mysql_connect($db_host, $db_username, $db_password, $new_link_id);
        }
        if (!$this->link_id) {
          $this->db_error('connect', $db_host);
        }
        // Valid db charset and set custom charset if it defined.
        // Select db.
        if ($this->link_id) {
          $this->valid_charset = @mysql_client_encoding($this->link_id);
          if (defined('DB_CHARSET') && DB_CHARSET != '')
            $this->query("SET NAMES ".DB_CHARSET);
          if (!@mysql_select_db($this->db_name, $this->link_id))
            $this->db_error('select', '');
          else
            return $this->link_id;
        }
        unset($db_host, $db_username, $db_password, $new_link_id, $pconnect, $db_name, $table_prefix);
      }


//////////////////////////////////////////////////////////////////////////////////
//connect using defaults set when the class started up
//////////////////////////////////////////////////////////////////////////////////
	public function connect_default() {
		DB::connect(DB::$db_current_host, DB::$db_current_username, DB::$db_current_password, false, false, DB::$db_current_db, '');
		}
		
    /**
	   * mysql_close();
	   * Close the database connection.
	   */
	    function close() {
        if ($this->link_id)
          @mysql_close($this->link_id);
      }

    /**
     * mysql_escape_string();
     * MySQL escape function.

      function escape($string) {
        if(version_compare(phpversion(),'4.3.0')=='-1') {
          return mysql_escape_string($string);
        } else {
          return mysql_real_escape_string($string);
        }
      }

      */

    /**
     * mysql_query();
     * Query the database.
	   * mysql_affected_rows, mysql_num_rows are in.
	   * @param string The SQL query to take action.
	   */
      function query($query) {
        $this->query_id = @mysql_query($query, $this->link_id);
        if (!$this->query_id) {
          echo '<p> '.$query.' <strong>query failed!</strong> </p>';
          exit();
        } else {
          // Get num rows.
		      $this->num_rows = @mysql_num_rows($this->query_id);
		      return $this->query_id;
		    }
        unset($query);
      }
	  
	  
	/**
	* insert_get_id
	* query with INSERT command
	* insert a record, and get the id number for the last record inserted
	*/
	  function insert_get_id($insert_query) {
	  	$this->query_id = $this->query($insert_query);
	  	return (mysql_insert_id($this->link_id)); //see if insert worked
	  }

    /**
     * table_exist
     * @desc Checks if table already exist in database.
     * @param string The table name to take action.
     */
      function table_exists($table_name = '') {
        $table = $this->query("SHOW TABLES LIKE '".$this->tbl_pre.$table_name ."'");
        if (@mysql_fetch_row($table) == false)
          return true;
        else
          return false;
        unset($table_name);
      }

    /**
     * optimize_table
     * @desc Optimize table after many operations.
     * @param string The table name to take action.
     */
      function optimize_table($table_name = '') {
        return $this->query("OPTIMIZE TABLE ".$this->db_name.".".$this->tbl_pre.$table_name);
      }

    /**
	   * mysql_fetch_array();
	   * Fetch a result row.
	   * @param resource The result of the query to take action.
	   */
      function fetch_array($result = -1) {
        if ($result != -1) {
          $this->query_id = $result;
        }
        if (isset($this->query_id)) {
          $this->record = @mysql_fetch_array($this->query_id);
        }
        if($this->record){
          $this->record = array_map('stripslashes', $this->record);
        }
        return $this->record;
        unset($result);
      }

    /**
	   * Fetch all rows.
	   * @param resource The result of the query to take action.
	   */
      function fetch_all_array($result = -1) {
      if ($result != -1) {
          $this->query_id = $result;
      }
      $out = array();
      while ($row = $this->fetch_array($this->query_id)){
          $out[] = $row;
      }
      $this->free_result($this->query_id);
      return $out;
      unset($result);
    }

    /**
     * mysql_free_result();
     * Free query.
     * @param string The query to take action.
     */
      function free_result($query) {
        return @mysql_free_result($query);
      }

    /**
     * kill_query
     * Kill the query.
     * @param string The query to take action.
     */
      function kill_query($query) {
        return $this->query("KILL $query");
      }

    /**
     * query_first
     * Fetches only first row.
     * @param string The query string to take action.
     */
      function query_first($result = -1) {
        if ($result != -1) {
          $query = $this->query($result);
        }
        $out = $this->fetch_array($query);
        $this->free_result($query);
        return $out;
        unset($result);
      }

    /**
	   * INSERT
	   * Insert query.
	   * @param string The table name.
	   * @param array An array of fields and values.
	   */
      function query_insert($table, $array) {
        $fields = '';
        $values = '';
        if(!is_array($array))
          return false;
		    foreach($array as $field => $value) {
			    $fields .= $field.", ";
			    $values .= "'".addslashes($value)."', ";
		    }
		    $fields = rtrim($fields, ', ');
		    $values = rtrim($values, ', ');
		    $this->query("
			    INSERT
			    INTO ".$this->tbl_pre.$table." (".$fields.")
			    VALUES (".$values.")
		    ");
        if ($this->query) {
		      return $this->query;
        } else {
          return false;
        }
        unset($array, $field, $value);
      }

    /**
	   * UPDATE
	   * Update query.
	   * @param string The table name.
	   * @param array An array of fields and values.
	   * @param string Where clause of the query.
	   */
      function query_update($table, $array, $where = '') {
        if(!is_array($array))
			    return false;
		    $query = '';
		    foreach($array as $field => $value) {
			    $query .= $field." = '".addslashes($value)."', ";
		    }
		    $query .= rtrim($query, ', ');
		    if($where != '') {
			    $query .= " WHERE $where";
		    }
		    $this->query("
			    UPDATE ".$this->tbl_pre.$table."
			    SET $query
		    ");
		    if ($this->query)
		      return $this->query;
        else
          return false;
        unset($array, $where, $field, $value);
      }

    /**
	   * DELETE
	   * Delete query.
	   * @param string The table name.
	   * @param string Where clause of the query.
	   */
      function query_delete($table = '', $where = ''){
        $query = !$where ? 'DELETE FROM '.$this->tbl_pre.$table : 'DELETE FROM '.$this->tbl_pre.$table.' WHERE '.$where;
        $this->query($query);
        unset($table, $where);
      }

    /**
     * ERROR
     * Error message.
	   * @param string Custom message text.
	   */
      function db_error($short = '', $param = '') {
        $short = preg_replace('/[^a-z0-9]/i', '', $short);
        $param = preg_replace('/[^a-z0-9]/i', '', $param);
        $errno = mysql_errno();
        $error = mysql_error();
        if ($errno == '')
          $errno = '<i>Unknown</i>';
        if ($error == '')
          $error = '<i>Unknown</i>';
        // Custom message text.
        if ($short == 'connect') {
          echo '
          <h2>MySQL Error</h2>
          <p><strong>MySQL error code</strong>: '.$errno.'</p>
          <p><strong>Error message</strong>: '.$error.'</p>
          <strong>Details</strong>: Failed to connect to database server <code>'.$param.'</code>.
          <h4>Please follow the following guidelines:</h4>
          <ul>
            <li><code>fos-config.php</code> file, the database user name and password, right?</li>
            <li><code>fos-config.php</code> file, the database server name right?</li>
            <li>Does your database server is running? If you are not sure what they mean ask your hosting company.</li>
          </ul> <br />
          <p class="help">If you need assistance, please visit <a href="" title="">Help Center</a>.</p>
          ';
        } elseif ($short == 'select') {
          echo '
          <h2>MySQL Error</h2>
          <p><strong>MySQL error code</strong>: '.$errno.'</p>
          <p><strong>Error message</strong>: '.$error.'</p>
          <strong>Details</strong>: Unable to select database <code>'.$this->db_name.'</code>.
          <h4>Please follow the following guidelines:</h4>
          <ul>
            <li>Does user <code>'.$this->db_username.'</code> have permission to use the <code>'.$this->db_name.'</code> database?</li>
            <li><code>fos-config.php</code> file, the database name right? If you are not sure what they mean ask your hosting company.</li>
          </ul> <br />
          <p class="help">If you need assistance, please visit <a href="" title="">Help Center</a>.</p>
          ';
        } else {
          echo '
          <h2>MySQL Error</h2>
          <p><strong>MySQL error code</strong>: '.$errno.'</p>
          <p><strong>Error message</strong>: '.$error.'</p>
          <strong>Details</strong>: <i>Unknown</i>.  <br />
          <p class="help">If you need assistance, please visit <a href="" title="">Help Center</a>.</p>
          ';
        }
        exit();
        unset($short, $param);
      }

  // End database class
  }

?>
