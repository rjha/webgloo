<?php

namespace com\indigloo\exception {

    class DBException extends \Exception  {

        public function __construct($message,$code = 1024) {
            parent::__construct($message,$code);
        }

    }
}

?>
