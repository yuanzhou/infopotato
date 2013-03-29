<?php
/**
 * Initial settings and global functions
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * @copyright Copyright &copy; 2009-2013 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 */
if (version_compare(PHP_VERSION, '5.3.0','<')) {
    exit('InfoPotato requires PHP 5.3.0 or newer.');
}

// Register given function as __autoload() implementation, PHP 5 >= 5.1.2
spl_autoload_register('auto_load');

// $_GET data is disallowed 
// InfoPotato utilizes URI segments rather than traditional URI query strings
unset($_GET);

// One key aspect of Web application security is referring to variables with precision
// one should not use $_REQUEST as it is less exact, and therefore less secure, 
// than explicitly referring to $_GET, $_POST and $_COOKIE
unset($_REQUEST);

// $_COOKIE can be used directly by InfoPotato's Cookie class or your own Cookie process 
// Remove backslashes added by magic quotes and return the user's raw input
// Normalizes all newlines to LF
// NOTE: $_SERVER and $_SESSION are not affected by magic_quotes
// $_POST, $_COOKIE, $_FILES and $_ENV were affected
$_COOKIE = isset($_COOKIE) ? sanitize($_COOKIE) : array();

/**
 * Sets the error_reporting directive at runtime
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In production environments, it is typically desirable to disable PHP's error reporting
 * by setting the internal error_reporting flag to a value of 0.
 *
 * As E_STRICT is not included within E_ALL you have to explicitly enable this kind of error level.
 * Most of E_STRICT errors are evaluated at the compile time thus such errors are not reported
 * in the file where error_reporting is enhanced to include E_STRICT errors (and vice versa).
 */
switch (ENVIRONMENT) {
    case 'development':
        // Show all errors, warnings and notices including coding standards
		// Note: E_STRICT became part of E_ALL in PHP 5.4.0
		if (version_compare(PHP_VERSION, '5.4.0') === -1) {
			// Same as error_reporting(E_ALL | E_STRICT);
			ini_set('error_reporting', E_ALL | E_STRICT);
		} else  {
			ini_set('error_reporting', E_ALL);
		}
        break;

    case 'production':
	    // Turn off all error reporting
		// Same as error_reporting(0);
		ini_set('error_reporting', 0);
        break;

    default:
        exit('The application environment is not set correctly.');
}

/**
 * SPL Autoloading required systen core components or app managers
 *
 * @param   string $class_name the class name we are trying to load
 * @return  void
 */   
function auto_load($class_name) {
    $class_name = strtolower($class_name);
	
    // Create and use core files to speed up the parsing process for all the following requests.
    // Init (loaded in entry point script) and Manager class ('manager', in the list below) are required for all app requests.
	// All core conponents must be listed is this array
    $core = array(
        'dispatcher', 
		'manager', 
        'dumper', 
		'logger', 
		'php_utf8',
        'i18n',
        'cookie',
        'session',
		'data', 
        'base_dao',
        'mysql_dao', 
        'mysqli_dao', 
        'postgresql_dao', 
        'sqlite_dao',
    );

    if (in_array($class_name, $core)) {
        $source_file = SYS_CORE_DIR.$class_name.'.php';
		
		// Checks if core component file exists
	    if ( ! file_exists($source_file)) { 
		    halt('An Error Was Encountered', "Missing core component file {$class_name}", 'sys_error');
	    }
		
		if (RUNTIME_CACHE === TRUE) {
            // The runtime folder must be writable
			$file = SYS_RUNTIME_CACHE_DIR.'~'.$class_name.'.php';
            if ( ! file_exists($file)) {
                // Return source with stripped comments and whitespace
				file_put_contents($file, php_strip_whitespace($source_file));
            }
        } else {
            $file = $source_file;
        }
    } else {
        // In some cases, an app manager could be a subclass of another app manager
		$source_file = APP_MANAGER_DIR.$class_name.'.php';
		
		// Checks if app manager file exists
	    if ( ! file_exists($source_file)) { 
		    halt('An Error Was Encountered', "Manager file {$class_name} does not exist", 'sys_error');
	    }
		
		if (RUNTIME_CACHE === TRUE) {
            // The runtime folder must be writable
			$file = APP_RUNTIME_CACHE_DIR.'~'.$class_name.'.php';
            if ( ! file_exists($file)) {
                // Return source with stripped comments and whitespace
				file_put_contents($file, php_strip_whitespace($source_file));
            }
        } else {
            $file = $source_file;
        }
    }

	// Using require_once() in the __autoload() function is redundant.  
	// __autoload() is only called when php can't find your class definition.  
	// If your file containg your class was already included, the class defenition would already be loaded 
	// and __autoload() would not be called.  So save a little overhead and only use require() within __autoload()
	require $file;
} 

/**
 * Display system error
 *
 * This function takes an error message as input,
 * log it to the defined file path and displays it using the specified template.
 * 
 * @param	string	the heading
 * @param	string	the message
 * @param	string	the template name
 * @param	int	the HTTP response status code
 * @return	string
 */
function halt($heading, $message, $template = 'sys_error') {
    // Log to caputre all errors since some errors can't be manually captured
    Logger::log_debug(APP_LOG_DIR, $message);
		
	if (ENVIRONMENT === 'development') {
		ob_start();
        require SYS_CORE_DIR.'sys_templates'.DS.$template.'.php';
        $buffer = ob_get_contents();
        ob_end_clean();
        echo $buffer;
        exit;
    }
	
	if (ENVIRONMENT === 'production') {
		// Display app specific 404 error page
		if (defined('APP_404_MANAGER') && defined('APP_404_MANAGER_METHOD')) {
			$uri = APP_URI_BASE.APP_404_MANAGER.'/'.APP_404_MANAGER_METHOD;
			$output = file_get_contents($uri);
			// To avoid soft 404, we need to send the 404 HTTP header status code 
			// before outputing the custom 404 content
			header('HTTP/1.1 404 Not Found');
			echo $output;
		}
        exit;
    }
}

/**
 * Dump variable
 *
 * Displays information about a variable in a human readable way
 * 
 * @param	mixed the variable to be dumped
 * @return	void
 */
function dump($var, $force_type = '', $collapsed = FALSE) {
    Dumper::dump($var, $force_type, $collapsed);
}

/**
 * Returns a translated string if one is found; Otherwise, the submitted message.
 * 
 * Translation/internationalization function. The PHP function
 * [strtr](http://php.net/strtr) is used for replacing parameters.
 *
 * __('Welcome back, :user', array(':user' => $username));
 *
 * The target language is defined by [I18n::$lang].
 * 
 * @uses    I18n::get
 * @param   string  text to translate
 * @param   array   values to replace in the translated text
 * @return  string
 */
function __($str, array $values = array()) {
    // Get the translation for this message
    $str = I18n::get($str);
    return empty($values) ? $str : strtr($str, $values);
}

/**
 * Recursively sanitizes an input variable:
 *
 * Strips slashes if magic quotes are enabled
 * Normalizes all newlines to LF
 *
 * @param   mixed  any variable
 * @return  mixed  sanitized variable
 */
function sanitize($value) {
    if (is_array($value)) {
        foreach ($value as $key => $val) {
            // Recursively clean each value
            $value[$key] = sanitize($val);
		}
	} 
	
	if (is_string($value)) {	
		// NOTE: Magic Quotes has been DEPRECATED as of PHP 5.3.0 and REMOVED as of PHP 5.4.0. 
		// And it will probably not exist in future versions at all.
		if (version_compare(PHP_VERSION, '5.4.0') === -1) {
			// When Magic Quotes are on (it's on by default), 
			// all ' (single-quote), " (double quote), \ (backslash) and NULL characters 
			// are escaped with a backslash automatically. This is identical to what addslashes() does.
			if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
				// Remove backslashes added by magic quotes and return the user's raw input
				$value = stripslashes($value);
			}
		}
		
		if (strpos($value, "\r") !== FALSE) {
			// Standardize newlines
			$value = str_replace(array("\r\n", "\r"), "\n", $value);
		}
	}

	return $value;
}

// End of file: ./system/core/init.php