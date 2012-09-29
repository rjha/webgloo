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
                array_unshift($rows,array('ui_code' => '', 'name' => '--'));
            }

            $buffer = '' ;
            $option = '<option value="{ui_code}" {flag}> {name}</option>' ;
            
            foreach($rows as $row) {

                $flag = (!is_null($default) && ($row['ui_code'] == $default))? 'selected' : '' ;
                $str = str_replace(array("{ui_code}","{name}","{flag}") ,
                                   array($row['ui_code'], $row['name'],$flag) , $option);
                $buffer = $buffer.$str ;
                                         
            }
                
            $buffer = '<select name="'.$name.'"> '.$buffer. ' </select>' ;
            return $buffer ;
        }

    }
    
}


?>
