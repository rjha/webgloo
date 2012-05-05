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
            //each application will read from its own config file
            $iniFile = $_SERVER['APP_CONFIG_PATH'];
            file_exists($iniFile) || die("unable to open app_config.ini file ");
            // create config object
            $this->ini_array = parse_ini_file($iniFile);
        }

        function get_value($key) {
            return $this->ini_array[$key];
        }

        function __destruct() {
            
        }
       
        function is_debug() {
            $val = $this->ini_array['debug.mode'];
            if (intval($val) == 1) {
                return true;
            } else {
                return false;
            }
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
