<?php
 class Data { protected $db; public function __construct($connection = '') { if ($connection !== '') { $this->db = self::_create_db_obj($connection); } } private static function _create_db_obj($connection) { static $db_obj = array(); $conn = explode(':', $connection); if (isset($db_obj[$connection])) { return $db_obj[$connection]; } if ( ! empty($conn)) { $data_source = require_once APP_CONFIG_DIR.'data_source.php'; if ( ! array_key_exists($conn[0], $data_source) || ! array_key_exists($conn[1], $data_source[$conn[0]])) { Global_Functions::show_sys_error('An Error Was Encountered', 'Incorrect database connection string', 'sys_error'); } $db_obj[$connection] = new $conn[0]($data_source[$conn[0]][$conn[1]]); return $db_obj[$connection]; } } } 