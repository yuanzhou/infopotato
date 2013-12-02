<?php
/**
 * Common shared functions
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * @copyright Copyright &copy; 2009-2013 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 */

namespace InfoPotato\core;

use InfoPotato\core\Logger;
use InfoPotato\core\Dumper;
use InfoPotato\core\I18n;

class Common {
    /**
     * Prevent direct object creation
     * 
     * @return Logger
     */
    private function __construct() {}
    
    /**
     * Display system error
     *
     * This function takes an error message as input,
     * log it to the defined file path and displays it using the specified template.
     * 
     * @param    string    the heading
     * @param    string    the message
     * @param    string    the template name
     * @return    string
     */
    public static function halt($heading, $message, $template = 'sys_error') {
        // Log to caputre all errors since some errors can't be manually captured
        Logger::log_debug(APP_LOG_DIR, $message);
            
        if (ENVIRONMENT === 'development') {
            ob_start();
            require SYS_CORE_DIR.'sys_templates'.DS.$template.'.php';
            $output = ob_get_contents();
            ob_end_clean();
        }
        
        if (ENVIRONMENT === 'production') {
            // Display app specific 404 error page if defined, 
            // otherwise use the system default template
            if (defined('APP_404_MANAGER') && defined('APP_404_MANAGER_METHOD')) {
                $output = file_get_contents(APP_URI_BASE.APP_404_MANAGER.'/'.APP_404_MANAGER_METHOD);
            } else {
                $output = file_get_contents(SYS_CORE_DIR.'sys_templates'.DS.'404_error.php');
            }
            // Send the 404 HTTP header status code to avoid soft 404 before outputing the custom 404 content
            // No need to send this 404 header status code under 'development' environment
            header('HTTP/1.1 404 Not Found');
        }
        
        echo $output;
        exit;
    }

    /**
     * Dump variable
     *
     * Displays information about a variable in a human readable way
     * 
     * @param    mixed the variable to be dumped
     * @param    force type for xml
     * @param    collapse or not
     * @return    void
     */
    public static function dump($var, $force_type = '', $collapsed = FALSE) {
        Dumper::dump($var, $force_type, $collapsed);
    }

}

// End of file: ./system/core/common.php