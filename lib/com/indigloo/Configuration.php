<?php

/*
 *
 * Abstraction for application specific configuration file.
 * we load only one instance of this class so do not try to load
 * two applications into same memory space. in other words two applications
 * setting different config file location will result in unstable/undefined behavior.
 * PHP  singleton implementation need not be thread safe!
 * I do not think there is even the concept of thread safe in PHP!
 *
 *
 *
 */

namespace com\indigloo {

    class Configuration {

        static private $instance = NULL;
        private $ini_array;

        static function getInstance() {

            if (self::$instance == NULL) {
                self::$instance = new Configuration();
            }

            return self::$instance;
        }

        function __construct() {

            // create config object
            // each application will read from its own config file
            file_exists(APP_CONFIG_PATH) || die("error opening config file ".APP_CONFIG_PATH);
            $this->ini_array = parse_ini_file(APP_CONFIG_PATH);

        }

        function get_value($key, $default=NULL) {

            $value = array_key_exists($key, $this->ini_array) ? $this->ini_array[$key] : $default;
            $value = empty($value) ? NULL : $value;
            return $value;
        
        }

        function __destruct() {
            
        }
       
        function is_debug() {

            $value = $this->ini_array['debug.mode'];
            $value = (intval($value) == 1) ? true : false; 
            return $value;

        }

        function log_level() {
            return $this->ini_array['log.level'];
        }

        function log_location() {
            return $this->ini_array['log.location'];
        }

        function max_file_size() {
            return $this->ini_array['max.file.size'];
        }
        
    }

}
?>
