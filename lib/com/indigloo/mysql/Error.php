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

        static function handle($mysqli) {

            $errorNo = $mysqli->errno;
            settype($errorNo,"integer");
            //error code zero means success
            if ($errorNo == 0 ) {
                return $errorNo;
            }
            
            //non zero error code means DB error
            throw new DBException($mysqli->error,$errorNo);

        }

    }

}
?>
