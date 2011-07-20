<?php
 switch (ENVIRONMENT) { case 'development': error_reporting(E_ALL | E_STRICT); break; case 'production': error_reporting(0); break; default: exit('The application environment is not set correctly.'); } function auto_load($class_name) { $class_name = strtolower($class_name); $runtime_list = array( 'dispatcher', 'manager', 'data', 'data_adapter', 'dumper', 'utf8', 'i18n', 'cookie', 'session', 'mysql_adapter', 'mysqli_adapter', 'postgresql_adapter', 'sqlite_adapter' ); if (in_array($class_name, $runtime_list)) { if (SYS_RUNTIME_CACHE === TRUE) { $file = SYS_RUNTIME_DIR.'~'.$class_name.'.php'; if ( ! file_exists($file)) { file_put_contents($file, php_strip_whitespace(SYS_CORE_DIR.$class_name.'.php')); } } else { $file = SYS_CORE_DIR.$class_name.'.php'; } } else { $file = APP_MANAGER_DIR.$class_name.'.php'; } require_once $file; return; } spl_autoload_register('auto_load'); function halt($heading, $message, $template = 'sys_error') { if (ENVIRONMENT === 'development') { ob_start(); require_once SYS_CORE_DIR.'sys_templates'.DS.$template.'.php'; $buffer = ob_get_contents(); ob_end_clean(); echo $buffer; exit; } } function dump($var, $force_type = '', $collapsed = FALSE) { Dumper::dump($var, $force_type, $collapsed); } function __($string, array $values = NULL) { $string = I18n::get($string); return empty($values) ? $string : strtr($string, $values); } function sanitize($value) { if (is_array($value)) { foreach ($value as $key => $val) { $value[$key] = sanitize($val); } } if (is_string($value)) { if (get_magic_quotes_gpc()) { $value = stripslashes($value); } if (strpos($value, "\r") !== FALSE) { $value = str_replace(array("\r\n", "\r"), "\n", $value); } } return $value; } function disable_register_globals() { if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) { echo "Global variable overload attack detected! Request aborted.\n"; exit(1); } $global_variables = array_keys($GLOBALS); $global_variables = array_diff($global_variables, array( '_COOKIE', '_ENV', '_GET', '_FILES', '_POST', '_REQUEST', '_SERVER', '_SESSION', 'GLOBALS', )); foreach ($global_variables as $name) { unset($GLOBALS[$name]); } } if (ini_get('register_globals')) { disable_register_globals(); } 