<?php

namespace com\indigloo\core {

    use \com\indigloo\Configuration as Config;
    use \com\indigloo\Logger as Logger;
    use \com\indigloo\connection\Redis as Redis ;

    /*
     * custom session handler to store PHP session data into redis DB
     *
     */

    class RedisSession {

        private $redis ;
        private $session_name ;

        function __construct() {

        }

        function open($path,$name) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_open : path=%s ,name %s",$path,$name);
                Logger::getInstance()->debug($message);
            }

            $this->redis = Redis::getInstance()->connection();
            $this->session_name = $name ;

            return TRUE ;
        }

        function close() {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_close called...");
                Logger::getInstance()->debug($message);
            }

            // some other code could have closed the redis connection
            // e.g. the site footer. so wrap the close call inside null check
            if(!is_null($this->redis)) {
                $this->redis->quit();
                $this->redis = NULL ;
            }

            return TRUE ;
        }

        private function makeKey($sessionId) {
            $key = "sc:".$this->session_name.":".$sessionId;
            return $key ;
        }

        function read($sessionId) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_read : session_id = %s",$sessionId);
                Logger::getInstance()->debug($message);
            }

            $data = "" ;

            $key = $this->makeKey($sessionId);
            $data = $this->redis->get($key);

            if (empty($data) || is_null($data)) {
                $data = "" ;
            }

            return $data ;
        }

        function write($sessionId,$data) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_write : session_id = %s",$sessionId);
                Logger::getInstance()->debug($message);
            }

            if(empty($data) || is_null($data)) { return ; }

            $key = $this->makeKey($sessionId);
            $lifetime = Config::getInstance()->get_value("session.lifetime",3600);
            $this->redis->setex($key, $lifetime, $data);

        }

        /*
         * destroy is called via session_destroy - this will happen on explicit logout
         * it is better to clear the stale sessions via a CRON script
         *
         */

        function destroy($sessionId) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_destroy : %s ",$sessionId);
                Logger::getInstance()->debug($message);
            }

            $key = $this->makeKey($sessionId);
            $this->redis->del($key);

        }

        function gc($age) {
            // we depend on redis to expire the keys
            // any custom gc logic should be called via a cron script
            // it is better to set session.gc_probability =0 in php.ini
        }

    }
}

?>
