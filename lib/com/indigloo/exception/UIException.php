<?php

namespace com\indigloo\exception {

    class UIException extends \Exception  {

        private $messages ;
        
        public function __construct($messages,$code = 0) {
            if(!is_array($messages) || (sizeof($messages) == 0))
                trigger_error("first argument to UIException is not an array",E_USER_ERROR);
            parent::__construct("UI Exception", $code);
            $this->messages = $messages;
        }

        public function getMessages() {
            return $this->messages ;
        }

    }
}

?>
