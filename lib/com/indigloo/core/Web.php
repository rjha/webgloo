<?php

/**
 *
 * @author rajeevj
 *
 * Web is a  class that lets you access request and context
 * and exposes other helper methods also. Only one instance is in effect
 * during processing of a request.
 *
 * This naming was influenced by web.py micro framework. This glue class lets
 * part of the system interact with each other much on the lines of web.py
 *
 *
 *
 */

namespace com\indigloo\core {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger as Logger;
    use com\indigloo\Util as Util;

    class Web {

        private $request;
        private $urls;
        static private $instance = NULL;
        
        private function __construct() {
            $this->request = new \com\indigloo\core\Request();
            $this->urls = array();
        }

        static function getInstance() {
            if (self::$instance == NULL) {
                self::$instance = new Web();
            }
            return self::$instance;
        }

        function getRequest() {
            return $this->request;
        }

        //request helper methods
        function getRequestParam($param) {
            return $this->request->getParam($param);
        }

        function getRequestAttribute($key) {
            return $this->request->getAttribute($key);
        }

        function setRequestAttribute($key, $value) {
            return $this->request->setAttribute($key, $value);
        }

        function store($key, $value) {

            if (isset($_SESSION)) {
                $_SESSION[$key] = $value;

                if (Config::getInstance()->is_debug()) {
                    Logger::getInstance()->debug('web :: storing in session :: key is:: ' . $key);
                    Logger::getInstance()->dump($value);
                }
            } else {
                trigger_error("No web session found", E_USER_ERROR);
            }
        }

        function find($key, $destroy=false) {
            $value = NULL;

            if (isset($_SESSION[$key]) && !empty($_SESSION[$key])) {
                $value = $_SESSION[$key];
                if (Config::getInstance()->is_debug()) {
                    Logger::getInstance()->debug('web :: fetching from session :: key is:: ' . $key);
                    Logger::getInstance()->dump($value);
                }

                if ($destroy) {
                    //remove this from session
                    $_SESSION[$key] = NULL;
                    if (Config::getInstance()->is_debug()) {
                        Logger::getInstance()->debug('web :: removed from session :: key is:: ' . $key);
                    }
                }
            }
            return $value;
        }

        function start() {

            if (Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug('web >> start >> hash is:: ' . spl_object_hash(self::$instance));
            }
        }

        function end() {
            //do not create a new instance
            $mysql = \com\indigloo\mysql\Connection::getInstance(false);
            if(!is_null($mysql)) {
                $mysql->closeHandle();
            }

            if (Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug('web >> end >> hash is:: ' . spl_object_hash(self::$instance));
            }


        }
    }

}
?>
