<?php
/**
 * SQLite(PDO) Data Access Object
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * @copyright Copyright &copy; 2009-2013 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 */
class SQLite_DAO extends Base_DAO {
	/**
	 * Database connection handler
	 *
	 * @var  object
	 */
	public $dbh;
	
	/**
	 * Constructor
	 * 
	 * Allow the user to perform a connect at the same time as initialising the this class
	 */
	public function __construct(array $config = array()) {
		// If there is no existing database connection then try to connect
		if ( ! is_object($this->dbh)) {
			try {
			    $this->dbh = new PDO($config['dsn']);
			} catch (PDOException $e) {
			    halt('An Error Was Encountered', 'Connection failed: '.$e->getMessage(), 'sys_error');
			}
		}
	}

	/** 
	 * Escapes special characters in a string for use in an SQL statement, 
	 * taking into account the current charset of the connection
	 */ 
	public function escape($string) { 
		// The input string should be un-quoted
		// is_string() will take '' as string
		if (is_string($string)) {
			$string = addslashes($string);
		}
		return $string; 
	}
	
	/** 
	 * USAGE: prepare( string $query [, array $params ] ) 
	 * The following directives can be used in the query format string:
	 * %d (decimal integer)
	 * %s (string)
	 * %f (float)
	 * 
	 * @return string the prepared SQL query
	 */ 
	public function prepare($query, array $params = NULL) { 
		// All variables in $params must be set before being passed to this function
		// if any variables are not set (will be NULL) will cause error in SQL
		if (count($params) > 0) { 			
			$pos_list = array();
			$pos_adj = 0;

			$bind_types = array('%s', '%d', '%f');

			foreach ($bind_types as $type) {
				$last_pos = 0;
				while (($pos = strpos($query, $type, $last_pos)) !== FALSE) {
					$last_pos = $pos + 1;
					if (isset($pos_list[$pos]) && strlen($pos_list[$pos]) > strlen($type)) {
						continue;
					}
					$pos_list[$pos] = $type;
				}
			}
			
			// By default $pos_list is ordered by the position of %s, %d, %f in the query
			// We need to reorder $pos_list so that it will be ordered by the key (position) from small to big
			ksort($pos_list);

			foreach ($pos_list as $pos => $type) {
				$type_length = strlen($type);

				$arg = array_shift($params);

				if ($type === '%s') {
					// Only single quote and escape string
				    // is_string() will take '' as string
					if (is_string($arg)) {
						$arg = "'".$this->escape($arg)."'";
					} else {
						halt('An Error Was Encountered', 'The binding value for %s must be a string', 'sys_error');
					}
				} elseif ($type === '%d') {
					if (is_int($arg)) {
						// Format the variable into a valid integer
						intval($arg);
					} else {
						halt('An Error Was Encountered', 'The binding value for %d must be an integer', 'sys_error');
					}
				} elseif ($type === '%f') {
					if (is_float($arg)) {
						// Format the variable into a valid float
						floatval($arg);
					} else {
						halt('An Error Was Encountered', 'The binding value for %f must be a float', 'sys_error');
					}
				} else {
					halt('An Error Was Encountered', "Unknown binding marker in: $query", 'sys_error');
				}

				$query = substr_replace($query, $arg, $pos + $pos_adj, $type_length);
				// Adjust the start offset for next replace
				$pos_adj += strlen($arg) - ($type_length);
			}
		} 
		return $query; 
	} 
	
	/**
	 * Perform a unique query (multiple queries are not supported) and try to determine result value
	 *
	 * @return int Number of rows affected/selected
	 */
	public function exec_query($query) {
		// Initialise return
		$return_val = 0;

		// Reset stored query result
		$this->query_result = array();

		// For reg expressions
		$query = trim($query);

		// Query was an insert, delete, drop, update, replace, alter
		if (preg_match("/^(insert|delete|drop|create|update|replace|alter)\s+/i", $query)) {
			// Execute the target query and return the number of affected rows
			// that were modified or deleted by the SQL statement you issued. 
			// If no rows were affected, returns 0.
			$rows_affected = $this->dbh->exec($query);

			// Take note of the last_insert_id
			// REPLACE works exactly like INSERT, except that if an old row in the table has the same value 
			// as a new row for a PRIMARY KEY or a UNIQUE index, the old row is deleted before the new row is inserted.
			if (preg_match("/^(insert|replace)\s+/i", $query)) {
				$this->last_insert_id = $this->dbh->lastInsertId();	
			}
			// Return number fo rows affected
			$return_val = $rows_affected;
		} elseif (preg_match("/^(select|describe|desc|show|explain)\s+/i", $query)) {
			// Executes an SQL statement, returns a PDOStatement object, or FALSE on failure.
			$statement = $this->dbh->query($query);
 
            if ($statement === FALSE) {
				$err = $this->dbh->errorInfo();
				halt('An Error Was Encountered', $err[0].' - '.$err[1].' - '.$err[2], 'sys_error');
			}
			
			// Store Query Results
			$num_rows = 0;
			while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
				// Store relults as an objects within main array
				// Convert to object
				$this->query_result[$num_rows] = (object) $row;
				$num_rows++;
			}

			// Log number of rows the query returned
			$this->num_rows = $num_rows;

			// Return number of rows selected
			$return_val = $this->num_rows;
		}

		return $return_val;
	}

	/**
	 * Begin Transaction using standard sql
	 *
	 * @access	public
	 * @return	bool
	 */
	public function trans_begin() {
		$this->dbh->beginTransaction();
	}
	
	/**
	 * Commit Transaction using standard sql
	 *
	 * @access	public
	 * @return	bool
	 */
	public function trans_commit() {
		$this->dbh->commit();
	}
	
	/**
	 * Rollback Transaction using standard sql
	 *
	 * @access	public
	 * @return	bool
	 */
	public function trans_rollback() {
		$this->dbh->rollBack();
	}
	
}

// End of file: ./system/core/sqlite_dao.php