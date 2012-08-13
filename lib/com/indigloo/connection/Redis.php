<?php

/**
 *
 * class to wrap a redis connection on top of redisent library
 * @see https://github.com/jdp/redisent
 *
 *
 */
namespace com\indigloo\connection {

    use com\indigloo\Logger as Logger;
    use com\indigloo\Configuration as Config ;

    class Redis {

         
        static private $instance = NULL;
        private $connx;

        private function __construct() {
            $this->connx = NULL ;
            $this->init();
        }

        private function init() {
            $dsn = Config::getInstance()->get_value("redis.dsn");
            $dsn = "redis://".$dsn ;
            $timeout = Config::getInstance()->get_value("redis.timeout");
            $this->connx = new \redisent\Redis($dsn,$timeout);
            
        }

        static function getInstance($flag=true) {
            if ($flag && is_null(self::$instance)) {
                self::$instance = new \com\indigloo\connection\Redis();
            }

            return self::$instance;
        }

        public function connection() {
            return $this->connx;
        }

        public function close() {
            if(!is_null($this->connx)) {
                $this->connx->quit();
                $this->connx = NULL ;
            }
            
            self::$instance == NULL;
        }

    }

}
?>
