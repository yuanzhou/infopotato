<?php
/**
 * MySQL ata Access Object
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * @copyright Copyright &copy; 2009-2011 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 */

class MySQL_DAO extends Base_DAO {
	/**
	 * Database host
	 *
	 * @var  string  
	 */
	public $dbhost = '';
	
	/**
	 * Database username
	 *
	 * @var  string  
	 */
	public $dbuser = '';
	
	/**
	 * Database user password
	 *
	 * @var  string  
	 */
	public $dbpass = '';
	
	/**
	 * Database to be used
	 *
	 * @var  string  
	 */
	public $dbname = '';
	
	/**
	 * Database table columns charset
	 *
	 * @var  string  
	 */
	public $charset;

	/**
	 * Database table columns collate
	 *
	 * @var  string  
	 */
	public $collate;

	/**
	 * Constructor
	 * 
	 * Allow the user to perform a connect at the same time as initialising the this class
	 */
	public function __construct($config = array()) {
		if (is_array($config) && count($config) > 0) {
			$this->dbuser = $config['user'];
			$this->dbpass = $config['pass'];
			$this->dbname = $config['name'];
			$this->dbhost = $config['host'];
			$this->charset = $config['charset'];
			$this->collate = $config['collate'];
		}
		
		// If there is no existing database connection then try to connect
		if ( ! $this->dbh) {
			$this->connect($this->dbuser, $this->dbpass, $this->dbname, $this->dbhost);
		}
	}
	
	/**
	 * Try to connect to MySQL database server
	 *
	 * @access	public
	 */
	public function connect($dbuser = '', $dbpass = '', $dbname = '', $dbhost = 'localhost') {
		$return_val = FALSE;

		// Only need to check $dbuser, because somethimes $dbpass = '' is permitted
		if ($dbuser === '') {
			halt('An Error Was Encountered', 'Require username to connect to a database server', 'sys_error');		
		} elseif ($dbname === '') {
			halt('An Error Was Encountered', 'Require database name to select a database', 'sys_error');		
		} elseif ( ! $this->dbh = mysql_connect($dbhost, $dbuser, $dbpass, TRUE)) {
			halt('An Error Was Encountered', 'Error establishing MySQL database connection. Correct user/password? Correct hostname? Database server running?', 'sys_error');		
		} else {
			if (function_exists('mysql_set_charset')) { 
				// Set charset (mysql_set_charset(), PHP 5 >= 5.2.3)
				mysql_set_charset($this->charset, $this->dbh);
			} else {
				// Specify the client encoding per connection
				$collation_query = "SET NAMES '{$this->charset}'";
				if ( ! empty($this->collate)) {
					$collation_query .= " COLLATE '{$this->collate}'";
				}
				$this->query($collation_query);
			}
			
			if ( ! mysql_select_db($dbname, $this->dbh)) {
				// Try to get error supplied by mysql if not use our own
				if ( ! $err_msg = mysql_error($this->dbh)) {
					$err_msg = 'Unexpected error while trying to select database';
				}
				halt('An Error Was Encountered', $err_msg, 'sys_error');		
			}
			
			$return_val = TRUE;
		}
		
		return $return_val;
	}
	
	/** 
	 * USAGE: prepare( string $query [, array $params ] ) 
	 * $query - SQL query WITHOUT any user-entered parameters. Replace parameters with "?" 
	 *     e.g. $query = "SELECT date from history WHERE login = ?" 
	 * $params - array of parameters 
	 * 
	 * Example: 
	 *    prepare( "SELECT secret FROM db WHERE login = ?", array($login) );  
	 *    prepare( "SELECT secret FROM db WHERE login = ? AND password = ?", array($login, $password) );  
	 * That will result safe query to MySQL with escaped $login and $password. 
	 */ 
	public function prepare($query, $params = array()) { 
		if (count($params) > 0) { 			
			foreach ($params as $v) { 
				if ($this->dbh) {
					$v = mysql_real_escape_string($v, $this->dbh); 
				} else {
					$v = addslashes($v);
				}
			} 	
			// in case someone mistakenly already singlequoted it
			$query = str_replace("'?'", '?', $query); 
			// in case someone mistakenly already doublequoted it
			$query = str_replace('"?"', '?', $query); 
			// quote the strings and replacing ? -> %s
			$query = str_replace('?', "'%s'", $query); 
			// vsprintf - replacing all %s to parameters 
			$query = vsprintf($query, $params);    
		} 
		return $query; 
	} 
	
	/**
	 * Perform MySQL query and try to detirmin result value
	 *
	 * @return int|FALSE Number of rows affected/selected or false on error
	 */
	public function query($query) {
		$return_val = 0;

		// Flush cached values.
		$this->flush();

		// For reg expressions
		$query = trim($query);

		// Keep track of the last query for debug.
		$this->last_query = $query;

		// Use core file cache function
		if ($cache = $this->get_cache($query)) {
			return $cache;
		}

		// For SELECT, SHOW, DESCRIBE, EXPLAIN and other statements returning resultset, 
		// mysql_query() returns a resource on success, or FALSE on error.
		// For INSERT, UPDATE, DELETE, DROP, etc, mysql_query() returns TRUE on success or FALSE on error.
		$result = mysql_query($query, $this->dbh);
		
		// If there is an error then take note of it.
		if ($err_msg = mysql_error($this->dbh)) {
			$is_insert = TRUE;
			halt('An Error Was Encountered', $err_msg, 'sys_error');		
			return FALSE;
		}

		// Query was an insert, delete, update, replace
		$is_insert = FALSE;
		if (preg_match('/^\s*(insert|delete|update|replace) /i', $query)) {
			$this->rows_affected = mysql_affected_rows($this->dbh);

			// Take note of the last_insert_id
			if (preg_match('/^\s*(insert|replace) /i', $query)) {
				$this->last_insert_id = mysql_insert_id($this->dbh);
			}
			// Return number fo rows affected
			$return_val = $this->rows_affected;
		} else {
			// Store Query Results
			$num_rows = 0;
			while ($row = mysql_fetch_object($result)) {
				// Store relults as an objects within main array
				$this->last_result[$num_rows] = $row;
				$num_rows++;
			}

			mysql_free_result($result);

			// Log number of rows the query returned
			$this->num_rows = $num_rows;

			// Return number of rows selected
			$return_val = $this->num_rows;
		}

		// disk caching of queries
		$this->store_cache($query, $is_insert);

		return $return_val;
	}

	/**
	 * Returns the current date and time, e.g., 2006-04-12 13:47:46
	 */
	public function now() {
		return 'NOW()';
	}
	
	/**
	 * Begin Transaction using standard sql
	 * MySQL MyISAM tables do not support transactions and will auto-commit even if a transaction has been started
	 * 
	 * @access	public
	 * @return	bool
	 */
	public function trans_begin() {
		mysql_query('SET AUTOCOMMIT=0', $this->dbh);
		mysql_query('START TRANSACTION', $this->dbh);// can also be BEGIN or BEGIN WORK
	}
	
	/**
	 * Commit Transaction using standard sql
	 *
	 * @access	public
	 * @return	bool
	 */
	public function trans_commit() {
		mysql_query('COMMIT', $this->dbh);
		mysql_query('SET AUTOCOMMIT=1', $this->dbh);
	}
	
	/**
	 * Rollback Transaction using standard sql
	 *
	 * @access	public
	 * @return	bool
	 */
	public function trans_rollback() {
		mysql_query('ROLLBACK', $this->dbh);
		mysql_query('SET AUTOCOMMIT=1', $this->dbh);
	}

}

// End of file: ./system/core/mysql_dao.php