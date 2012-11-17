<?php
namespace com\indigloo\mysql{

    use \com\indigloo\Configuration as Config ;
    
    /*
     * to close the connx to database, set $dbh = NULL inside your application code
     * from the PHP manual 
     * ------------------------------------------
     * 
     * Upon successful connection to the database, an instance of the PDO class
     * is returned to your script. The connection remains active for the lifetime
     * of that PDO object. To close the connection, you need to destroy the object 
     * by ensuring that all remaining references to it are deleted--you do this by 
     * assigning NULL to the variable that holds the object. If you don't do this 
     * explicitly, PHP will automatically close the connection when your script ends.
     * 
     * ---------------------------------------------
     *
     */

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
