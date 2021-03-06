<?php
/**
 * File-based Output Cache Library
 *
 * @author Zhou Yuan <yuanzhou19@gmail.com>
 * @link http://www.infopotato.com/
 * @copyright Copyright &copy; 2009-2014 Zhou Yuan
 * @license http://www.opensource.org/licenses/mit-license.php MIT Licence
 * @link based on http://www.jongales.com/blog/2009/02/18/simple-file-based-php-cache-class/
 * @link based on http://www.rooftopsolutions.nl/article/107
 */
 
namespace InfoPotato\libraries\output_cache;

class Output_Cache_Library {  
    /**
     * The cache directory
     * The dir should end with DIRECTORY_SEPARATOR or DS (defined in bootstrap)
     * 
     * @var string 
     */
    private $cache_dir = '';
    
    /**
     * Constructor
     *
     * The constructor can be passed an array of config values
     */
    public function __construct(array $config = NULL) {
        if (count($config) > 0) {
            foreach ($config as $key => $val) {
                // Using isset() requires $this->$key not to be NULL in property definition
                // property_exists() allows empty property
                if (property_exists($this, $key)) {
                    $method = 'initialize_'.$key;
                    
                    if (method_exists($this, $method)) {
                        $this->$method($val);
                    }
                } else {
                    exit("'".$key."' is not an acceptable config argument!");
                }
            }
        }
    }
    
    /**
     * Validate and set $cache_dir
     *
     * @param $val string
     * @return void
     */
    private function initialize_cache_dir($dir_path) {
        if ( ! is_string($dir_path)) {
            $this->invalid_argument_value('cache_dir');
        }
        $this->cache_dir = $dir_path;
    }
    
    /**
     * Output the error message for invalid argument value
     *
     * @return void
     */
    private function invalid_argument_value($arg) {
        exit("In your config array, the provided argument value of "."'".$arg."'"." is invalid.");
    }
    
    /**
     * Find the filename for a certain key 
     *
     * @param string
     * @return string
     */
    private function name($key) {  
        return ($this->cache_dir).sha1($key);  
    }  
    
    /**
     * Get the cached file for a certain key 
     * 
     * @param $key string
     * @return mixed - FALSE|string (cached data)
     */
    public function get($key) {  
        if ( ! is_dir($this->cache_dir)) {
            return FALSE;   
        }
        $cache_path = $this->name($key);  
        
        if ( ! file_exists($cache_path) || ! is_readable($cache_path)) {
            return FALSE;   
        }
        // Open for reading only; use 'b' to force binary mode
        if ( ! $fp = fopen($cache_path, 'rb')) {
            return FALSE;  
        }
        // To acquire a shared lock (reader)
        flock($fp, LOCK_SH);  
        // file_get_contents() is much faster than fread()
        // $cached_data is an array contains [0] => expire time, [1] => cached content
        $cached_data = unserialize(file_get_contents($cache_path));  
        // To release a lock (shared or exclusive)
        flock($fp, LOCK_UN);
        // The lock is released also by fclose() (which is also called automatically when script finished).
        fclose($fp);
        
        if ($cached_data) {
            // Check if the cached file was expired 
            if (time() > $cached_data[0]) { 
                $this->clear($key);  
                return FALSE;
            }
            return $cached_data[1]; 
        } 
        return FALSE; 
    }  
    
    /**
     * Create the cache file for a certain key 
     * 
     * @param $key string
     * @param $data string the data to be cached
     * @param $ttl integer - time to life (seconds)
     * @return bool
     */
    public function set($key, $data, $ttl = 3600) {  
        if ( ! is_dir($this->cache_dir) || ! is_writable($this->cache_dir)) {
            return FALSE;  
        }
        $cache_path = $this->name($key);  
        
        // Open for writing only; use 'b' to force binary mode
        if ( ! $fp = fopen($cache_path, 'wb')) {
            return FALSE;    
        }
        // To acquire an exclusive lock (writer)
        if (flock($fp, LOCK_EX)) {      
            // fwrite is faster than file_put_contents() 
            fwrite($fp, serialize(array(time() + $ttl, $data)));  
            // To release a lock (shared or exclusive)
            flock($fp, LOCK_UN);
            // The lock is released also by fclose() (which is also called automatically when script finished).
            fclose($fp); 
        } else {
            return FALSE;  
        }
        chmod($cache_path, 0777); 
        return TRUE;    
    }
    
    /**
     * Delete the cached file for a certain key 
     *
     * @param $key string
     * @return bool
     */
    public function clear($key) {  
        $cache_path = $this->name($key);  
        if (file_exists($cache_path)) {  
            // Deletes cached file
            unlink($cache_path);  
            return TRUE;  
        }  
        return FALSE;  
    }  
    
}

/* End of file: ./system/libraries/output_cache/output_cache_library.php */