<?php
 class Data { protected $db; public function __construct($connection = '') { if ($connection !== '') { $this->db = self::_create_db_obj($connection); } } private static function _create_db_obj($connection) { static $db_obj = array(); $conn = explode(':', $connection); if (isset($db_obj[$connection])) { return $db_obj[$connection]; } if ( ! empty($conn)) { $data_source = require_once APP_CONFIG_DIR.'data_source.php'; $db_obj[$connection] = new $conn[0]($data_source[$conn[0]][$conn[1]]); return $db_obj[$connection]; } } } 