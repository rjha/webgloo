<?php
namespace com\indigloo\mysql{

    use \com\indigloo\Configuration as Config ;
    
    class PDOWrapper {

        static function getHandle() {

            $host = Config::getInstance()->get_value("mysql.host");
            $dbname = Config::getInstance()->get_value("mysql.database");
            $dsn = sprintf("mysql:host=%s;dbname=%s",$host,$dbname);

            $user = Config::getInstance()->get_value("mysql.user");
            $password = Config::getInstance()->get_value("mysql.password");
            $dbh = new \PDO($dsn, $user, $password);

            //throw exceptions
            $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            return $dbh ;
        }
    }


}
?>
