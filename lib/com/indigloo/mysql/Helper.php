<?php

/**
 *
 * @author rajeevj
 */

namespace com\indigloo\mysql {

    use com\indigloo\Logger;
    use com\indigloo\Configuration as Config;
    use com\indigloo\mysql as MySQL;

    class Helper {

        static function fetchRows($mysqli, $sql) {

            if (is_null($sql) || is_null($mysqli)) {
                trigger_error(" Fatal: Null mysqli connx or null SQL supplied", E_USER_ERROR);
            }

            $rows = NULL;
            $result = $mysqli->query($sql);
            if ($result) {
                $rows = array();
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    array_push($rows, $row);
                }
            } else {
                trigger_error($mysqli->error, E_USER_ERROR);
            }

            $result->free();
            if (Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug(" Fetch rows SQL >> " . $sql);
                Logger::getInstance()->debug(" number of rows >> " . sizeof($rows));
            }

            return $rows;
        }

        static function fetchRow($mysqli, $sql) {

            if (is_null($sql) || is_null($mysqli)) {
                trigger_error(" Fatal: Null mysqli connx or null SQL supplied", E_USER_ERROR);
            }

            $row = NULL;
            $result = $mysqli->query($sql);
            if ($result) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
            } else {
                trigger_error($mysqli->error, E_USER_ERROR);
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
                trigger_error($mysqli->error, E_USER_ERROR);
            }
        }
        
        static function addLimitSQL($sql, $pageNo,$pageSize) {
            $offset = 0 + ($pageNo - 1 ) * $pageSize;
            $sql = $sql." LIMIT  " .$offset. "," .$pageSize;
            return $sql ;
        }
        
    }

}
?>
