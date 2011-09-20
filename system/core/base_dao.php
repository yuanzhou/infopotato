<?php
/**
 * Base Database Access Object
 * Members must be public in order to be called in data object
 * The following databases are supported:
 *
 *  - [http://mysql.com MySQL]
 *  - [http://postgresql.org PostgreSQL]
 *  - [http://sqlite.org SQLite]
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * Original code from {@link http://php.justinvincent.com Justin Vincent (justin@visunet.ie)}
 * @copyright Copyright &copy; 2009-2011 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 */
 
 
// Must define the constants out of the class
// Can not define them as class const 
define('OBJECT', 'OBJECT');
define('ARRAY_A', 'ARRAY_A');
define('ARRAY_N', 'ARRAY_N');

class Base_DAO {
	/**
	 * Query result
	 *
	 * @var  array  
	 */
	public $query_result = array();

	/**
	 * Gets the ID generated by the last INSERT query
	 *
	 * @var  integer  
	 */
	public $last_insert_id;

	/**
	 * Constructor
	 * 
	 * Closing isn't usually necessary, as non-persistent open links are automatically closed at the end of the script's execution.
	 * Allow the user to perform a connect at the same time as initialising the this class
	 */
	public function __construct(array $config = NULL) {}
	
	/** 
	 * Overridden by specific DB class
	 * USAGE: prepare( string $query [, array $params ] ) 
	 * $query - SQL query WITHOUT any user-entered parameters. Replace parameters with "?" 
	 *     e.g. $query = "SELECT date from history WHERE login = ?" 
	 * $params - array of parameters 
	 * 
	 * Example: 
	 *    prepare( "SELECT secret FROM db WHERE login = ?", array($login) );  
	 *    prepare( "SELECT secret FROM db WHERE login = ? AND password = ?", array($login, $password) );  
	 * That will result safe query to RDBMS with escaped $login and $password. 
	 */ 
	public function prepare($query, array $params = NULL) {} 
	
	/**
	 * Perform SQL query and try to determine result value
	 * Overridden by specific DB class
	 * The query string should not end with a semicolon
	 *
	 * @return int Number of rows affected/selected or false on error
	 */
	public function exec_query($query) {}

	/**
	 * Returns the current date and time, e.g., 2006-04-12 13:47:46
	 * Overridden by specific DB class
	 */
	public function now() {}

	/**
	 * Gets one single variable from the database
	 *
	 * This function is very useful for evaluating query results within logic statements such as if or switch. 
	 * If the query generates more than one row the first row will always be used by default.
	 * If the query generates more than one column the leftmost column will always be used by default.
	 * Even so, the full resultset will be available within the array $db->query_result should you wish to use them.
	 *
	 * @param string $query SQL query. If null, use the result from the previous query.
	 * @param int $x (optional) Column of value to return.  Indexed from 0.
	 * @param int $y (optional) Row of value to return.  Indexed from 0.
	 * @return string Database query result
	 */
	public function get_var($query, $x = 0, $y = 0) {
		$return_val = NULL;
		
		if ($query) {
		    $this->exec_query($query);
		}
		
		if ($this->query_result !== array()) {
		    if ($y >= count($this->query_result)) {
				halt('A System Error Was Encountered', 'The offset y you specified overflows', 'sys_error');
			}
			
			// Returns all the values from the input array and indexes numerically the array
			$values = array_values(get_object_vars($this->query_result[$y]));
			if ($x >= count($values)) {
				halt('A System Error Was Encountered', 'The offset x you specified overflows', 'sys_error');
			}
			
			if ($values[$x] !== '') {
			    $return_val = $values[$x];
			}
		}
		
		return $return_val;
	}

	/**
	 * Gets a single row from the database
	 * If the query returns more than one row and no row offset is supplied the first row within the results set will be returned by default.
	 *
	 * @param string $query SQL query.
	 * @param string $output (optional) one of ARRAY_A | ARRAY_N | OBJECT constants.  Return an associative array (column => value, ...), a numerically indexed array (0 => value, ...) or an object ( ->column = value ), respectively.
	 * @param int $y (optional) Row to return if the query returns more than one row.  Indexed from 0.
	 * @return mixed Database query result in format specifed by $output
	 */
	public function get_row($query, $output = OBJECT, $y = 0) {
		$return_val = NULL;
		
		$this->exec_query($query);
		
		if ($this->query_result !== array()) {
		    if ($y >= count($this->query_result)) {
				halt('A System Error Was Encountered', 'The offset y you specified overflows', 'sys_error');
			}
			
			if ($output == OBJECT) {
				$return_val = $this->query_result[$y];
			} elseif ($output == ARRAY_A) {
				$return_val = get_object_vars($this->query_result[$y]);
			} elseif ($output == ARRAY_N) {
				$return_val = array_values(get_object_vars($this->query_result[$y]));
			} else {
				halt('A System Error Was Encountered', " \$db->get_row(string query, output type, int offset) -- Output type must be one of: OBJECT, ARRAY_A, ARRAY_N", 'sys_error');
			}
		}
		
		return $return_val;
	}

	/**
	 * Extracts one column as one dimensional array based on a column offset.
	 *
	 * If no offset is supplied the offset will defualt to column 0. I.E the first column.
	 * If a null query is supplied the previous query results are used.
	 *
	 * @param string $query SQL query.  If null, use the result from the previous query.
	 * @param int $x Column to return.  Indexed from 0.
	 * @return array Database query result.  Array indexed from 0 by SQL result row number.
	 */
	public function get_col($query, $x = 0) {
		$return_val = array();
		
		$this->exec_query($query);
		
		// Extract the column values
		$cnt = count($this->query_result);
		for ($i = 0; $i < $cnt; $i++) {
			$return_val[$i] = $this->get_var(NULL, $x, $i);
		}
		
		return $return_val;
	}


	/**
	 * Gets multiple rows of results from the database based on query and returns them as a multi dimensional array.
	 *
	 * Each element of the array contains one row of results and can be specified to be either an object, associative array or numerical array.
	 * If no results are found then the function returns false enabling you to use the function within logic statements such as if.
	 *
	 * @param string $query SQL query.
	 * @param string $output (optional) ane of ARRAY_A | ARRAY_N | OBJECT constants.  
	 * @return mixed Database query results
	 */
	public function get_results($query, $output = OBJECT) {
		$return_val = NULL;
		
		$this->exec_query($query);
		
		// Send back array of objects. Each row is an object
		if ($output == OBJECT) {
			$return_val = $this->query_result;
		} elseif ($output == ARRAY_A || $output == ARRAY_N) {
			if ($this->query_result) {
				$i = 0;
				// $row is object
				foreach($this->query_result as $row) {
					$new_array[$i] = get_object_vars($row);

					if ($output == ARRAY_N) {
						$new_array[$i] = array_values($new_array[$i]);
					}
					$i++;
				}
				$return_val = $new_array;
			}
		}
		
		return $return_val;
	}

	/**
	 * Begin Transaction
	 *
	 * @return	bool
	 */
	public function trans_begin() {}
	
	/**
	 * Commit Transaction
	 *
	 * @return	bool
	 */
	public function trans_commit() {}
	
	/**
	 * Rollback Transaction
	 *
	 * @return	bool
	 */
	public function trans_rollback() {}

}

// End of file: ./system/core/base_dao.php