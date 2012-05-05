<?php
namespace com\indigloo\core {
    
    use \com\indigloo\Configuration as Config;

    /*
     * custom session handler to store PHP session data into mysql DB
     * we use a -select for update- row leve lock 
     *
     */
    class MySQLSession {

        private $mysqli ;

        function __construct() {

        }
        
        function open($path,$name) {
            $this->mysqli = new \mysqli(Config::getInstance()->get_value("mysql.host"),
                            Config::getInstance()->get_value("mysql.user"),
                            Config::getInstance()->get_value("mysql.password"),
                            Config::getInstance()->get_value("mysql.database")); 

            if (mysqli_connect_errno ()) {
                trigger_error(mysqli_connect_error(), E_USER_ERROR);
                exit(1);
            }

            //remove old sessions
            $this->gc(1440);
           
            return TRUE ;
        }

        function close() {
            $this->mysqli->close();
            $this->mysqli = null;
            return TRUE ;
        }

        function read($sessionId) {
            //start Tx
            $this->mysqli->query("START TRANSACTION"); 
            $sql = " select data from sc_php_session where session_id = '%s'  for update ";
            $sessionId = $this->mysqli->real_escape_string($sessionId);
            $sql = sprintf($sql,$sessionId);

            $result = $this->mysqli->query($sql);
            $data = '' ;

            if ($result) {
                $record = $result->fetch_array(MYSQLI_ASSOC);
                $data = $record['data'];
            } 

            $result->free();
            return $data ;

        }

        function write($sessionId,$data) {

            $sessionId = $this->mysqli->real_escape_string($sessionId);
            $data = $this->mysqli->real_escape_string($data);

            $sql = "REPLACE INTO sc_php_session(session_id,data,updated_on) VALUES('%s', '%s', now())" ;
            $sql = sprintf($sql,$sessionId, $data);

            $stmt = $this->mysqli->prepare($sql);
            if ($stmt) {
                $stmt->execute();
                $stmt->close();
            } else {
                trigger_error($this->mysqli->error, E_USER_ERROR);
            }
            //end Tx
            $this->mysqli->query("COMMIT"); 

        }

        function destroy($sessionId) {
            $sessionId = $this->mysqli->real_escape_string($sessionId);
            $sql = "DELETE FROM sc_php_session WHERE session_id = '%s' ";
            $sql = sprintf($sql,$sessionId);

            $stmt = $this->mysqli->prepare($sql);
            if ($stmt) {
                $stmt->execute();
                $stmt->close();
            } else {
                trigger_error($this->mysqli->error, E_USER_ERROR);
            }
        }

        /* 
         * @param $age - number in seconds set by session.gc_maxlifetime value
         * default is 1440 or 24 mins.
         *
         */
        function gc($age) {
            $sql = "DELETE FROM sc_php_session WHERE updated_on < (now() - INTERVAL %d SECOND) ";
            $sql = sprintf($sql,$age);
            $stmt = $this->mysqli->prepare($sql);
            if ($stmt) {
                $stmt->execute();
                $stmt->close();
            } else {
                trigger_error($this->mysqli->error, E_USER_ERROR);
            }

        }

    }
}
?>
