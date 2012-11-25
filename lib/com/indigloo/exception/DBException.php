<?php

namespace com\indigloo\exception {

    class DBException extends \Exception  {

        public function __construct($message,$code=0, \Exception $previous = null) {
            // PDO exception etc. can return strange string codes
            // Exception expects an integer error code.
            settype($code,"integer");
            parent::__construct($message,$code,$previous);
        }

    }
}

?>
