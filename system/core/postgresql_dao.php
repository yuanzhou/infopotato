<?php
/**
 * PostgreSQL Data Access Object
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * @copyright Copyright &copy; 2009-2014 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 */

namespace InfoPotato\core;

class PostgreSQL_DAO extends Base_DAO {
    /**
     * Database connection handler
     *
     * @var resource  
     */
    private $dbh;
    
    /**
     * Constructor
     * 
     * Allow the user to perform a connect at the same time as initialising the this class
     */
    public function __construct(array $config = array()) {
        // If there is no existing database connection then try to connect
        if ( ! is_resource($this->dbh)) {
            if ( ! $this->dbh = pg_connect($config['dsn'])) {
                Common::halt('An Error Was Encountered', 'Could not connect: '.pg_last_error($this->dbh), 'sys_error');
            } 

            // Specify the client encoding per connection
            $collation_query = "SET NAMES '{$config['charset']}'";
            $this->exec_query($collation_query);
        }
    }

    /** 
     * Escapes special characters in a string for use in an SQL statement, 
     * taking into account the current charset of the connection
	 *
	 * @param string $str the raw query string
	 * @return string the escaped query string
     */
    public function escape($str) {
        // Only escape string
        // is_string() will take '' as string
        if (is_string($str)) {
            if (version_compare(PHP_VERSION, '5.4.4', '<')) {
                $str = pg_escape_string($this->dbh, $str);
            } else  {
                // Use of pg_escape_literal() is recommended instead of pg_escape_string()
                $str = pg_escape_literal($this->dbh, $str);
            }
        }
        return $str; 
    }
    
    /** 
     * USAGE: prepare( string $query [, array $params ] ) 
     * The following directives can be used in the query format string:
     * %i (integer)
     * %s (string)
     * %f (float)
     * 
	 * @param string $query the raw query string that contains directives
     * @param array $params an array of values to replace the directives
     * @return string the prepared SQL query
     */ 
    public function prepare($query, array $params = NULL) { 
        // All variables in $params must be set before being passed to this function
        // if any variables are not set (will be NULL) will cause error in SQL
        if (count($params) > 0) {
            $pos_list = array();
            $pos_adj = 0;

            $bind_types = array('%s', '%i', '%f');

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
            
            // By default $pos_list is ordered by the position of %s, %i, %f in the query
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
                        Common::halt('An Error Was Encountered', 'The binding value for %s must be a string!', 'sys_error');
                    }
                } elseif ($type === '%i') {
                    // 32 bit systems have a maximum signed integer range of -2147483648 to 2147483647. 
                    // So is_int(2147483648) will return FALSE in 32 bit systems.
                    // 64 bit systems have a maximum signed integer range of -9223372036854775808 to 9223372036854775807. 
                    // So is_int(9223372036854775808) will return FALSE in 64 bit systems.
                    if ( ! is_int($arg)) {
                        Common::halt('An Error Was Encountered', 'The binding value for %i must be an integer!', 'sys_error');
                    }
                } elseif ($type === '%f') {
                    if (is_float($arg)) {
                        // E.g., is_float(1e7) returns TRUE since 1e7 is a float in Scientific Notation
                        // We need to use floatval() to get the float value of the given variable
                        floatval($arg);
                    } else {
                        Common::halt('An Error Was Encountered', 'The binding value for %f must be a float!', 'sys_error');
                    }
                } else {
                    Common::halt('An Error Was Encountered', "Unknown binding marker in: $query", 'sys_error');
                }

                // Note that strlen() simply counts the number of bytes in a string, not the number of characters. 
                // This means for UTF-8 string the integer it returns is actually longer than the number of characters in the string.
                // BUT because $pos is not affected by the actual replacement string and 
                // $pos_adj only represents the query length increment after each replacement,
                // and at the same time substr_replace() counts exactly the same as strlen(), 
                // the prepared final query will be no problem.
                $query = substr_replace($query, $arg, $pos + $pos_adj, $type_length);
                // Adjust the start offset for next replacement
                $pos_adj += strlen($arg) - $type_length;
            }
        } 
        
        return $query; 
    } 
    
    /**
     * Perform a unique query (multiple queries are not supported) and try to determine result value
     *
     * @param string $query the raw query string
     * @return int the number of rows affected/selected or false on error
     */
    public function exec_query($query) {
        // Initialize return
        $return_val = 0;

        // Reset stored query result
        $this->query_result = array();

        // It's safe to use trim() without the second charlist argument on a UTF-8 string,
        // because the whitespace characters they are searching for are all in the ASCII 7 range.
        $query = trim($query);

        // A query result resource on success or FALSE on failure.
        $result = pg_query($this->dbh, $query);

        // If there is an error then take note of it.
        if ($err_msg = pg_last_error($this->dbh)) {
            Common::halt('An Error Was Encountered', $err_msg, 'sys_error');        
        }

        // Query was an insert, delete, drop, update, replace, alter
        if (preg_match("/^(insert|delete|drop|update|replace|alter)\s+/i", $query)) {
            $rows_affected = pg_affected_rows($result);

            // Take note of the last_insert_id
            // REPLACE works exactly like INSERT, except that if an old row in the table has the same value 
            // as a new row for a PRIMARY KEY or a UNIQUE index, the old row is deleted before the new row is inserted.
            if (preg_match("/^(insert|replace)\s+/i", $query)) {
                $this->last_insert_id = pg_last_oid($result);
            }
            // Return number fo rows affected
            $return_val = $rows_affected;
        } elseif (preg_match("/^(select|describe|desc|show|explain)\s+/i", $query)) {
            // Store Query Results
            $num_rows = 0;
            while ($row = pg_fetch_object($result)) {
                // Store relults as an objects within main array
                $this->query_result[$num_rows] = $row;
                $num_rows++;
            }

            pg_free_result($result);

            // Log number of rows the query returned
            $this->num_rows = $num_rows;

            // Return number of rows selected
            $return_val = $this->num_rows;
        } elseif (preg_match("/^create\s+/i", $query)) {
            // Table creation returns TRUE on success, or FALSE on error.
            $return_val = $result;
        }

        return $return_val;
    }

    /**
     * Begin Transaction using standard sql
     *
     * @return bool
     */
    public function trans_begin() {
        return (pg_query($this->dbh, 'begin') !== FALSE) ? TRUE : FALSE;
    }
    
    /**
     * Commit Transaction using standard sql
     *
     * @return bool
     */
    public function trans_commit() {
        return (pg_query($this->dbh, 'commit') !== FALSE) ? TRUE : FALSE;
    }
    
    /**
     * Rollback Transaction using standard sql
     *
     * @return bool
     */
    public function trans_rollback() {
        return (pg_query($this->dbh, 'rollback') !== FALSE) ? TRUE : FALSE;
    }
    
}

// End of file: ./system/core/postgresql_dao.php