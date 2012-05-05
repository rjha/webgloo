<?php

namespace com\indigloo\ui\form {

    class Sticky {

        private $data;

        function __construct($data) {
            $this->data = $data;
        }
        
        function get($key, $default='') {
            if (!is_null($this->data)
                    && (sizeof($this->data) > 0 )
                    && array_key_exists($key, $this->data)) {
                return $this->data[$key];
            } else {
                return $default;
            }
        }

    }

}
?>
