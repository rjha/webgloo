<?php

namespace com\indigloo\mail {

    use com\indigloo\Util as Util ;
    use com\indigloo\Configuration as Config ;

    class SendGrid {

        static function sendViaWeb($tos,$from,$fromName,$subject,$text,$html){
            $login = Config::getInstance()->get_value("sendgrid.login");
            $password = Config::getInstance()->get_value("sendgrid.password");

            // SendGrid PHP LIB path should be included before
            // webgloo libraries for this to work
            $sendgrid = new \SendGrid($login,$password);
            $mail = new \SendGrid\Mail();

            $fromName = empty($fromName) ? $from : $fromName ;
            $mail->setTos($tos)->
                setFrom($from)->
                setFromName($fromName)->
                setSubject($subject)->
                setText($text)->
                setHtml($html);

            $response = $sendgrid->web->send($mail);
            //@todo - error handling

        }
    }
}
?>
