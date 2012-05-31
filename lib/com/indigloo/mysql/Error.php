<?php

/**
 *
 * @author rajeevj
 * @see also http://dev.mysql.com/doc/refman/5.0/en/error-messages-server.html
 *
 */

namespace com\indigloo\mysql {

    use \com\indigloo\Logger;
    use \com\indigloo\mysql as MySQL;
    use com\indigloo\exception\DBException ;

    class Error {

        static function handle($dbHandle) {

            $errorNo = $dbHandle->errno;
            //error code zero means success
            if (empty($errorNo)) {
                return $errorNo;
            }
            
            //non zero error code means DB error
            $message = sprintf("DB error :: code: %d  message: %s \n",$errorNo,$dbHandle->error);
            throw new DBException($message);

        }

    }

}
?>
