<?php

namespace com\indigloo\ui\form {

    use com\indigloo\core\Web as Web;
    use com\indigloo\Constants as Constants ;
    
    class Message {
        
        
        function __construct($data) {
            
        }
        
        static function render() {
            $gWeb = Web::getInstance();
            $messages = $gWeb->find(Constants::FORM_MESSAGES,true);
            $errors = $gWeb->find(Constants::FORM_ERRORS,true);
            
            if(sizeof($errors) > 0 ) {
                printf("<div class=\"alert\">");
                printf("<a class=\"close\" data-dismiss=\"alert\" href=\"#\">x</a>");
                printf("<ul>");
                foreach($errors as $error) {
                    printf("<li>  %s </li>", $error);
                }
                printf("</ul>");
                printf("</div>");
                
            }
            
            if(sizeof($messages) > 0 ) {
                printf("<div class=\"alert alert-success\">");
                printf("<a class=\"close\" data-dismiss=\"alert\" href=\"#\">x</a>");
                printf("<ul>");
                foreach($messages as $message) {
                    printf("<li>  %s </li>", $message);
                }
                printf("</ul>");
                printf("</div>");
                
            }
            
            
        }

    }

}
?>
