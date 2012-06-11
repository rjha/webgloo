<?php

/**
 *
 * @author rajeevj
 */

namespace com\indigloo\mysql {

    use \com\indigloo\Logger;
    use \com\indigloo\Configuration as Config;
    use \com\indigloo\mysql as MySQL;
    use com\indigloo\exception\DBException ;

    class Helper {

        static function fetchRows($mysqli, $sql) {

            if (is_null($mysqli)) {
                throw new DBException("Fatal :: Null mysqli connection supplied");
            }

            if (is_null($sql) || is_null($mysqli)) {
                throw new DBException("Fatal :: Null SQL supplied");
            }

            $rows = NULL;
            $result = $mysqli->query($sql);
            if ($result) {
                $rows = array();
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    array_push($rows, $row);
                }
            } else {
                 throw new DBException($mysqli->error,$mysqli->errno);
            }

            $result->free();
            if (Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug(" Fetch rows SQL >> " . $sql);
                Logger::getInstance()->debug(" number of rows >> " . sizeof($rows));
            }

            return $rows;
        }

        static function fetchRow($mysqli, $sql) {

            if (is_null($mysqli)) {
                throw new DBException("Fatal :: Null mysqli connection supplied");
            }

            if (is_null($sql) || is_null($mysqli)) {
                throw new DBException("Fatal :: Null SQL supplied");
            }

            $row = NULL;
            $result = $mysqli->query($sql);
            if ($result) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
            } else {
                throw new DBException($mysqli->error,$mysqli->errno);
            }
            
            $result->free();
            if (Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug(" Row SQL >> " . $sql);
            }

            return $row;
        }

        static function executeSQL($mysqli, $sql) {
            if (Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug("execute SQL >> " . $sql);
            }

            $stmt = $mysqli->prepare($sql);
            if ($stmt) {
                $stmt->execute();
                $stmt->close();
            } else {
                throw new DBException($mysqli->error,$mysqli->errno);
            }
        }

    }

}
?>
