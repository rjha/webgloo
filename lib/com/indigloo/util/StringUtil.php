<?php
namespace com\indigloo\util{

    class StringUtil {

        static function convertNameToKey($name) {

            if(is_null($name)) {
                trigger_error("wrong token supplied!", E_USER_ERROR);
            }

            $name = trim($name);
            $size = strlen($name);

            $buffer = '' ;
            $ch = '' ;
            $flag = false ;

            //first pass - collect alphanumeric and treat others as spaces
            for($i = 0; $i < $size ; $i++ ){
                $ch = $name[$i];
                if(ctype_alnum($ch)) {
                    $buffer .= $ch ;
                    $flag = false ;
                }else {
                    if(!$flag){
                        $buffer .= '-';
                        $flag = true ;
                    }
                }  
            }

            //convert lowercase
            $buffer = strtolower($buffer);
            $buffer = trim($buffer);
            return $buffer ;

        }

        static function convertKeyToName($key) {
            if(is_null($key)) {
                trigger_error("wrong token supplied!", E_USER_ERROR);
            }

            $key = trim($key);
            $size = strlen($key);
 
            $buffer = '' ;
            $ch = '' ;

            for($i = 0; $i < $size ; $i++ ){

                $ch = $key[$i];

                if($ch == '-') {
                    $buffer .= ' ' ;
                } else {
                    $buffer .= $ch ;
                }
            }

            $buffer = ucwords($buffer);
            $buffer = trim($buffer);
            return $buffer ;
        }



    }
}
?>
