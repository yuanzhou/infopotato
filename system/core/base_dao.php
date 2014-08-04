<?php
/**
 * Abstract Base Database Access Object
 * 
 * Members must be public in order to be called in data object
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * Original code from {@link http://php.justinvincent.com Justin Vincent (justin@visunet.ie)}
 * @copyright Copyright &copy; 2009-2014 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 */
 
namespace InfoPotato\core;

abstract class Base_DAO {
    /**
     * Query result
     *
     * @var array  
     */
    protected $query_result = array();
    
    /**
     * Gets the ID generated by the last INSERT query
     *
     * @var int 
     */
    protected $last_insert_id;
    
    /**
     * Constructor
     * Allow the user to perform a connect at the same time as initialising the this class
     */
    abstract protected function __construct(array $config = NULL);
    
    /** 
     * Escapes special characters in a string for use in an SQL statement, 
     * taking into account the current charset of the connection
	 * To be implemented by each specific DAO class
	 * 
	 * @param string $string the raw query string
	 * @return string the escaped query string
     */ 
    abstract protected function escape($string);
    
    /** 
     * To be implemented by each specific DAO class
     * USAGE: prepare( string $query [, array $params ] ) 
     * The following directives can be used in the query format string:
     * %d (decimal integer)
     * %s (string)
     * %f (float)
	 * 
	 * @param string $query the raw query string that contains directives
     * @param array $params an array of values to replace the directives
     * 
     * @return string the prepared SQL query
     */ 
    abstract protected function prepare($query, array $params = NULL);
    
    /**
     * Perform SQL query and try to determine result value
     * To be implemented by each specific DAO class
     * The query string should not end with a semicolon
     *
	 * @param string $query the raw query string
     * @return int the number of rows affected/selected or false on error
     */
    abstract protected function exec_query($query);

    /**
     * Gets one single data cell from the database
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
    protected function get_cell($query, $x = 0, $y = 0) {
        $return_val = '';
        
        $this->exec_query($query);
        // If result is not an empty array
        if ($this->query_result !== array()) {
            if ($y >= count($this->query_result)) {
                Common::halt('A System Error Was Encountered', 'The offset y you specified overflows', 'sys_error');
            }
            
            // Returns all the values from the input array and indexes numerically the array
            $values = array_values(get_object_vars($this->query_result[$y]));
            if ($x >= count($values)) {
                Common::halt('A System Error Was Encountered', 'The offset x you specified overflows', 'sys_error');
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
     * @param string $output (optional) one of 'FETCH_ASSOC' | 'FETCH_NUM' | 'FETCH_OBJ' constants.  
     * @param int $y (optional) Row to return if the query returns more than one row.  Indexed from 0.
     * @return mixed Database query result in format specified by $output
     */
    protected function get_row($query, $output = 'FETCH_OBJ', $y = 0) {
        $return_val = NULL;
        
        $this->exec_query($query);
        // If result is not an empty array
        if ($this->query_result !== array()) {
            if ($y >= count($this->query_result)) {
                Common::halt('A System Error Was Encountered', 'The offset y you specified overflows', 'sys_error');
            }
            
            if ($output === 'FETCH_OBJ') {
                $return_val = $this->query_result[$y];
            } elseif ($output === 'FETCH_ASSOC') {
                $return_val = get_object_vars($this->query_result[$y]);
            } elseif ($output === 'FETCH_NUM') {
                $return_val = array_values(get_object_vars($this->query_result[$y]));
            } else {
                Common::halt('A System Error Was Encountered', " \$db->get_row() -- Output type must be one of: 'FETCH_OBJ', 'FETCH_ASSOC', 'FETCH_NUM'", 'sys_error');
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
    protected function get_col($query, $x = 0) {
        $return_val = array();
        
        $this->exec_query($query);
        // If result is not an empty array
        if ($this->query_result !== array()) {
            // Extract the column values
            $cnt = count($this->query_result);

            if ($x >= count(get_object_vars($this->query_result[0]))) {
                Common::halt('A System Error Was Encountered', 'The offset x you specified overflows', 'sys_error');
            }
                
            for ($i = 0; $i < $cnt; $i++) {
                // Returns all the values from the input array and indexes numerically the array
                $values = array_values(get_object_vars($this->query_result[$i]));
                if ($values[$x] !== '') {
                    $return_val[$i] = $values[$x];
                }
            }
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
     * @param string $output (optional) one of 'FETCH_ASSOC' | 'FETCH_ASSOC' | 'FETCH_OBJ' constants.  
     * @return mixed Database query results
     */
    protected function get_all($query, $output = 'FETCH_OBJ') {
        $return_val = array();
        
        $this->exec_query($query);
        
        // Send back array of objects. Each row is an object
        if ($output === 'FETCH_OBJ') {
            $return_val = $this->query_result;
        } elseif ($output === 'FETCH_ASSOC' || $output === 'FETCH_NUM') {
            if ($this->query_result) {
                $i = 0;
                // $row is object
                foreach($this->query_result as $row) {
                    $return_val[$i] = get_object_vars($row);

                    if ($output === 'FETCH_NUM') {
                        $return_val[$i] = array_values($return_val[$i]);
                    }
                    $i++;
                }
            }
        } else {
            Common::halt('A System Error Was Encountered', " \$db->get_all() -- Output type must be one of: 'FETCH_OBJ', 'FETCH_ASSOC', 'FETCH_NUM'", 'sys_error');
        }
        
        return $return_val;
    }
    
    /**
     * Returns the auto generated id used in the last query
     *
     * @return int
     */
    protected function last_insert_id() {
        return $this->last_insert_id;
    }
    
    /**
     * Begin Transaction
     * To be implemented by each specific DAO class
	 *
     * @return bool
     */
    abstract protected function trans_begin();
    
    /**
     * Commit Transaction
     * To be implemented by each specific DAO class
	 *
     * @return bool
     */
    abstract protected function trans_commit();
    
    /**
     * Rollback Transaction
     * To be implemented by each specific DAO class
	 *
     * @return bool
     */
    abstract protected function trans_rollback();
    
}

// End of file: ./system/core/base_dao.php