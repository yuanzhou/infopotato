<?php
final class SQLite_Manager extends Manager {
	public function get_index() {
		$this->load_data('sqlite_data', 'u');
		
		//$user_info = $this->u->get_user_info(1);
		$users_info = $this->u->get_users_info();
		
		//$this->u->add_user('dsadas', 'dadsasada');
		
		//Global_Functions::dump($user_info);
		Global_Functions::dump($users_info);
	}
}

// End of file: ./application/managers/sqlite_manager.php
