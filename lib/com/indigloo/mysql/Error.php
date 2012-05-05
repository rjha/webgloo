<?php

/**
 *
 * @author rajeevj
 * @see also http://dev.mysql.com/doc/refman/5.0/en/error-messages-server.html
 * 
 */

namespace com\indigloo\mysql {

    use com\indigloo\Logger;
    use com\indigloo\mysql as MySQL;

    class Error {

        static function handle($module, $dbHandle) {

            $errorNo = $dbHandle->errno;
            //error code zero means success
            if (empty($errorNo)) {
                return $errorNo;
            }

            $map = array( 1062 => MySQL\Connection::DUPLICATE_KEY);
            $message = sprintf(" DB error code : %d  message : %s \n",$errorNo,$dbHandler->error);
            
            // errors returned to upper layers
            if (array_key_exists($errorNo, $map)) {
                Logger::getInstance()->error($message);
                $code = $map[$errorNo];
                return $code;
            } else {
                //crash and burn errors
                trigger_error($message, E_USER_ERROR);
                exit(1);
            }
        }

    }

}
?>
