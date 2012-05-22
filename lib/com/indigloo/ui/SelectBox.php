<?php

namespace com\indigloo\ui {

    use com\indigloo\core\Web as Web;
    use com\indigloo\Util as Util;
    use com\indigloo\Constants as Constants ;
    
    class SelectBox {
        
        
        static function render($rows,$options) {
           
            $name = Util::getArrayKey($options,'name');
            $default = Util::tryArrayKey($options,'default');
            $showEmpty = Util::tryArrayKey($options,'empty');

            if(!is_null($showEmpty) && $showEmpty) {
                array_unshift($rows,array('code' => '', 'display' => '--'));
            }

            $buffer = '' ;
            $option = '<option value="{code}" {flag}> {display}</option>' ;
            
            foreach($rows as $row) {

                $flag = (!is_null($default) && ($row['code'] == $default))? 'selected' : '' ;
                $str = str_replace(array("{code}","{display}","{flag}") ,
                                   array($row['code'], $row['display'],$flag) , $option);
                $buffer = $buffer.$str ;
                                         
            }
                
            $buffer = '<select name="'.$name.'"> '.$buffer. ' </select>' ;
            return $buffer ;
        }

    }
    
}


?>
