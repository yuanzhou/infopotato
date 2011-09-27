<?php
class SQLite_Data extends Data {
	public function __construct() {
		// Use default database connection config
		parent::__construct('sqlite_dao:test');
	}

	public function get_user_info($id) { 
		$sql = $this->db->prepare("SELECT * FROM t1 WHERE id=?", array($id));
		//$this->db->vardump($this->db->get_row($sql, FETCH_ASSOC));

		return $this->db->get_row($sql, FETCH_ASSOC);
	}
	
	public function get_users_info() { 
		$sql = $this->db->prepare("SELECT * FROM t1");
		//$this->db->vardump($this->db->get_results($sql, FETCH_ASSOC));
		return $this->db->get_all($sql, FETCH_ASSOC);
	}
	
	public function add_user($short, $long) {
		$return_val = TRUE;
		
		$sql = $this->db->prepare("INSERT INTO t1 (short, long) 
								   VALUES (?, ?)", array($short, $long));
		if ($this->db->query($sql) === FALSE) {
			$return_val = FALSE;
		}	
		//$this->db->vardump($this->db->query($sql));
		return $return_val;
	}
}

/* End of file: ./application/data/sqlite_data.php */
