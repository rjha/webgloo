<?php

namespace com\indigloo\exception {

    class DBException extends \Exception  {

        public function __construct($message) {
            parent::__construct($message);
        }

    }
}

?>
