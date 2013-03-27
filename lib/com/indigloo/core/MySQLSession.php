<?php

namespace com\indigloo\core {

    use \com\indigloo\Configuration as Config;
    use \com\indigloo\mysql\PDOWrapper;
    use \com\indigloo\Logger as Logger;

    /*
     * custom session handler to store PHP session data into mysql DB
     * we use a -select for update- row level lock
     *
     * @todo : supply session store table name from outside
     * 
     */
    class MySQLSession {

        private $dbh ;

        function __construct() {

        }

        function open($path,$name) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_open : path=%s name %s",$path,$name);
                Logger::getInstance()->debug($message);
            }

            $this->dbh = PDOWrapper::getHandle();
            return TRUE ;
        }

        function close() {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_close called... ");
                Logger::getInstance()->debug($message);
            }

            $this->dbh = null;
            return TRUE ;
        }

        function read($sessionId) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_read : session_id = %s",$sessionId);
                Logger::getInstance()->debug($message);
            }

            //start Tx
            $this->dbh->beginTransaction();
            $sql = " select data from sc_php_session where session_id = :session_id  for update ";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(":session_id",$sessionId, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $data = "" ;
            if($result) {
                $data = $result["data"];
            }

            return $data ;
        }

        function write($sessionId,$data) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_write  : session_id = %s",$sessionId);
                Logger::getInstance()->debug($message);
            }

            if(empty($data) || is_null($data)) {
                //end Tx
                $this->dbh->commit();
                return ;
            }

            $sql = " select count(session_id) as total from sc_php_session where session_id = :session_id" ;
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(":session_id",$sessionId, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $total = $result['total'];

            if($total > 0) {
                //existing session
                $sql2 = " update sc_php_session set data = :data, updated_on = now() where session_id = :session_id" ;
            } else {
                $sql2 = "insert INTO sc_php_session(session_id,data,updated_on) VALUES(:session_id, :data, now())" ;
            }

            $stmt2 = $this->dbh->prepare($sql2);
            $stmt2->bindParam(":session_id",$sessionId, \PDO::PARAM_STR);
            $stmt2->bindParam(":data",$data, \PDO::PARAM_STR);
            $stmt2->execute();

            //end Tx
            $this->dbh->commit();
            return TRUE ;
        }

        /*
         * destroy is called via session_destroy
         * However it is better to clear the stale sessions via a CRON script
         */

        function destroy($sessionId) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_destroy : %s ",$sessionId);
                Logger::getInstance()->debug($message);
            }

            $sql = "DELETE FROM sc_php_session WHERE session_id = :session_id ";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(":session_id",$sessionId, \PDO::PARAM_STR);
            $stmt->execute();
            return TRUE ;

        }

        /*
         * @param $age - number in seconds set by session.gc_maxlifetime value
         * default is 1440 or 24 mins.
         *
         */
        function gc($age) {

            if(Config::getInstance()->is_debug()) {
                $message= sprintf("session_gc : age  %d ",$age);
                Logger::getInstance()->debug($message);
            }

            $sql = "DELETE FROM sc_php_session WHERE updated_on < (now() - INTERVAL :age SECOND) ";
            $stmt = $this->dbh->prepare($sql);
            $stmt->bindParam(":age",$age, \PDO::PARAM_INT);
            $stmt->execute();
            return TRUE ;
        }

    }
}

?>
