<?php

/**
 *
 *
 * Logger is our own implementation of logger classes.
 * Earlier we were using PEAR Log but that package is not compatible
 * with PHP5 E_STRICT MODE.
 * @see also http://www.indelible.org/php/Log/guide.html
 *
 * @author rajeevj
 * 
 */


namespace com\indigloo {

    use com\indigloo\Configuration as Config ;
    use com\indigloo\Util ;

    class Logger {

        static private $instance = NULL;
        private $sysLevel;
        private $fhandle;
        private $priority;
        private $isDebug = false;

        const ERROR_PRIORITY = 4;
        const WARN_PRIORITY = 3;
        const INFO_PRIORITY = 2;
        const DEBUG_PRIORITY = 1;

        private function __construct() {
            
            $logfile = Config::getInstance()->log_location();
            
            if (!file_exists($logfile)) {
                //create the file
                $this->fhandle = fopen($logfile, "x+");
                
            } else {
                $this->fhandle = fopen($logfile, "a+");
            }
            
            $this->sysLevel = Config::getInstance()->log_level();
            $this->priority = $this->level_to_priority($this->sysLevel);
            $this->isDebug = Config::getInstance()->is_debug();
        }

        function __destruct() {
            fclose($this->fhandle);
        }

        function level_to_priority($level) {

            $priority = Logger::ERROR_PRIORITY;
            if (is_null($level) || empty($level)) {
                return $priority;
            }
            $level = strtoupper($level);

            switch ($level) {
                case 'DEBUG' :
                    $priority = Logger::DEBUG_PRIORITY;
                    break;
                case 'INFO' :
                    $priority = Logger::INFO_PRIORITY;
                    break;

                case 'WARN' :
                    $priority = Logger::WARN_PRIORITY;
                    break;

                case 'ERROR' :
                    $priority = Logger::ERROR_PRIORITY;
                    break;
                default :
                    $priority = Logger::ERROR_PRIORITY;
                    break;
            }

            return $priority;
        }

        static function getInstance() {
            if (self::$instance == NULL) {
                self::$instance = new Logger();
            }

            return self::$instance;
        }

        function debug($message) {
            if (!$this->isDebug) {
                return;
            }
            
            if (intval(Logger::DEBUG_PRIORITY) >= $this->priority) {
                $this->logIt($message, 'debug');
            }
            
        }

        function info($message) {

            if (intval(Logger::INFO_PRIORITY) >= $this->priority) {
                $this->logIt($message, 'info');
            }
        }

        function warning($message) {

            if (intval(Logger::WARN_PRIORITY) >= $this->priority) {
                $this->logIt($message, 'warning');
            }
        }

        function error($message) {

            if (intval(Logger::ERROR_PRIORITY) >= $this->priority) {
                $this->logIt($message, 'error');
            }
        }

        function backtrace($trace=NULL) {
            fwrite($this->fhandle," ---- __backtrace__  ----- \n" );
            if(is_null($trace))
                $trace = debug_backtrace();
            $message = '' ;

            foreach ($trace as $i=>$t) {
                //class and type is not always available in trace
                $class = isset($t['class']) ? $t['class'] : '#class#' ;
                $type = isset($t['type']) ? $t['type'] : ' ' ;
                $function = isset($t['function']) ? $t['function'] : '#function#' ;
                $file = isset($t['file']) ? $t['file'] : '#file#' ;
                $line = isset($t['line']) ? $t['line'] : '#line#' ;

                $message = sprintf("#%d %s%s%s() called at %s:%d \n", 
                    $i,
                    $class, 
                    $type,
                    $function,
                    $file,
                    $line);

                fwrite($this->fhandle,$message);
                
                if ($this->isDebug) {
                    if(isset($t['args']) && !empty($t['args'])) {
                        $this->dump($t['args']);
                    } 
                }
            }

            fwrite($this->fhandle," --------- \n\n" );
        }

        function logIt($message, $level) {
            $logMessage = sprintf("%s - %s - %s \n",  date("d.m.Y H:i:s"), $level,$message);
            fwrite($this->fhandle,$logMessage);
        }

		/* use to dump variables inside an error condition only */

		function dump($var) {
            // print_r will print in human readable form only
			// Also, with print_r you should not forget to reset the array pointer
			// though right now that is not required but documentation mentions that 
			// https://bugs.php.net/bug.php?id=54931 
            //
			// Logger::dump will __not__ work inside an ob_start callback function 
			// callback is only needed if you want to modify the content of a buffer (like gzipping)
			// we should be fine since we do not call ob_start with callback anywhere
            //
            // var_export has issues with PDO - Nesting level too deep error 
            // @see https://bugs.php.net/bug.php?id=30471 
			//

            $message = var_export($var,true);
            fwrite($this->fhandle,$message."\n");
		}

    }

}
?>
