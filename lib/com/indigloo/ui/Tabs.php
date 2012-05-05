<?php

namespace com\indigloo\ui {

    use \com\indigloo\Util as Util;
    use \com\indigloo\Url as Url;
    
    class Tabs {
        
        static function render($options,$default) {
            $tab = Url::tryQueryParam("tab",$default);

            if(Util::tryEmpty($tab)) {
                $tab = $default ;
            }

            if(!array_key_exists($tab,$options)) {
                $tab = $default;
            }

            $buffer = '' ;
            $item = '<li class="%s"> <a href="%s">%s</a></li>';
             
            foreach($options as $key=>$value) {
                $pageURI = Url::addQueryParameters($_SERVER['REQUEST_URI'],array("tab" => $key));
                $class = ($tab == $key) ? 'active' : '' ;
                $strItem = sprintf($item,$class,$pageURI,$value);
                $buffer .= $strItem;
            }
                
            $buffer = '<ul class="nav nav-tabs">'.$buffer.'</ul>' ;
            $data = array("buffer" => $buffer, "active"=> $tab);
            return $data ;
        }

    }
    
}


?>
