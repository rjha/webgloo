<?php



namespace com\indigloo\mysql {

    use com\indigloo\Logger as Logger;
    use com\indigloo\Configuration as Config ;

    class Connection {

        private $numCalls;
        static private $instance = NULL;
        private $mysqli;
        private $connxId;
        
        //DB  codes
        const ACK_OK = 0;
        const DUPLICATE_KEY = 1;

        private function __construct() {
            $this->numCalls = 0;
            $this->initDataBase();
        }

        static function getInstance() {
            if (self::$instance == NULL) {
                self::$instance = new Connection();
            }

            return self::$instance;
        }
    
        public function getHandle() {
            $this->numCalls++;
            if (Config::getInstance()->is_debug()) {
                $message = 'Acquire mysql handle - ' . $this->connxId . ',call no - ' . $this->numCalls;
                Logger::getInstance()->debug($message);
            }
            return $this->mysqli;
        }

        public function closeHandle() {
            if ($this->numCalls > 0) {
                $this->mysqli->close();
            }
            if (Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug('Closed mysql handle - ' . $this->connxId);
            }

            self::$instance == NULL;
        }
        
        public function getLastInsertId() {
            return $this->mysqli->insert_id ;
        }

        private function initDataBase() {

            $this->mysqli = new \mysqli( Config::getInstance()->get_value("mysql.host"),
                            Config::getInstance()->get_value("mysql.user"),
                            Config::getInstance()->get_value("mysql.password"),
                            Config::getInstance()->get_value("mysql.database"));

            if (mysqli_connect_errno ()) {
                trigger_error(mysqli_connect_error(), E_USER_ERROR);
                exit(1);
            }

            $this->connxId = spl_object_hash($this->mysqli);

            if (Config::getInstance()->is_debug()) {
                $message = 'Created mysql connx - ' . $this->connxId;
                Logger::getInstance()->debug($message);
            }
        }

    }

}
?>
