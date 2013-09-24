<?php

namespace com\indigloo\ui {

    use com\indigloo\core\Web as Web;
    use com\indigloo\Constants as Constants ;
    
    class Page {
        
        function __construct() {
            
        }
	
        static function addMessage($message) {
        	$gWeb = Web::getInstance();
        	$messages = $messages = $gWeb->find(Constants::PAGE_MESSAGES);
        	
        	if(is_null($messages) || empty($messages) || !is_array($messages)) {
        		$messages = array();
        		array_push($messages,$message);
        		$gWeb->store(Constants::PAGE_MESSAGES,$messages);
        	} else {
        		array_push($messages,$message);
        		$gWeb->store(Constants::PAGE_MESSAGES,$messages);
        	}
        	
        }
        
        static function messageJson() {
        	$gWeb = Web::getInstance();
        	$messages = $gWeb->find(Constants::PAGE_MESSAGES,true);
        	return json_encode($messages);
        }
        
        static function messageHtml() {
        	$gWeb = Web::getInstance();
        	$messages = $gWeb->find(Constants::PAGE_MESSAGES,true);
        	
        	if(is_array($messages) && (sizeof($messages) > 0)) {
        		
        		printf("<ul>");
        		foreach($messages as $message) {
        			printf("<li>  %s </li>", $message);
        		}
        		printf("</ul>");
        		
        	}
    
        }
        
    }

}
?>
