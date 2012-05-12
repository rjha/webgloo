<?php
namespace com\indigloo\ui {
    
    class Filter {
        const EQ = '=' ;
        const GT =  '>' ;
        const LT = '<' ;
        const LTE = '<=' ;
        const GTE = '>=' ;
        const LIKE = 'like' ;

        private $model;
        private $map ;
         
        function __construct($model) {
             $this->map = array();
             $this->map["model"] = $model ;
        }

        function add($name,$condition,$value) {
            $this->map["name"] = $name ;
            $this->map["condition"] = $condition ;
            $this->map["value"] = $value ;
        }

        function sanitize($sanitized){
            //sanitized filter input 
            $this->map["value"] = $sanitized;
        }

        function __get($name) {
            if(array_key_exists($name,$this->map) && !empty($this->map[$name])) {
                return $this->map[$name] ;
            } else {
                return NULL;
            }
        }
    }
}

?>
