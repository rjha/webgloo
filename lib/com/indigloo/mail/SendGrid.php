<?php

namespace com\indigloo\mail {

    use com\indigloo\Util as Util ;
    use com\indigloo\Configuration as Config ;
    use com\indigloo\Logger as Logger ;

    /**
     * class to wrap sendgrid-php web API.
     * sendgrid-php web API uses curl to communicate to sendgrid endpoint.
     *
     * @see SendGrid/Web#send($mail) 
     * @see http://docs.sendgrid.com/documentation/api/web-api/mail/#send
     *
     */

    class SendGrid {

        const BAD_INPUT_ERROR = 1 ;
        const CURL_ERROR = 2 ;
        const MALFORMED_RESPONSE = 3 ;
        const SENDGRID_ERROR = 4 ;
        const UNKNOWN_ERROR = 5 ;


        /**
         *
         * @param $tos an array containing to addresses, is required.
         * @param from : sender's email address, is required
         * @param fromName : human friendly sender's name 
         * @param subject required
         * @param text - text content of mail, is required
         * @param html - html content of mail, is required 
         *
         * @return return value of zero indicates success
         * A non zero return value indicates failure.
         *
         */
        static function sendViaWeb($tos,$from,$fromName,$subject,$text,$html){

            $mode = Config::getInstance()->get_value("sendgrid.mail.mode");
            if(strcmp($mode,"production") != 0) {
                $recipients = implode($tos, ",");
                $message = sprintf("\n\n sendgrid mail to %s \n %s \n\n",$recipients,$text);
                Logger::getInstance()->info($message);
                return ;
            }

            $login = Config::getInstance()->get_value("sendgrid.login");
            $password = Config::getInstance()->get_value("sendgrid.password");

            if(empty($login) 
                || empty($password)
                || empty($tos)
                || empty($from)
                || empty($text)
                || empty($html)) {

                //bad input
                return self::BAD_INPUT_ERROR ;
            }

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

            
            /* 
             * response handling.
             * CURLOPT_RETURNTRANSFER option is set in SendGrid/Web#send()
             * that method will return the result on success, FALSE on failure
             *
             * @see http://docs.sendgrid.com/documentation/api/web-api/#responseserror
             * {"message":"error","errors":[]}
             *
             * @see http://docs.sendgrid.com/documentation/api/web-api/#responsessuccess
             * {"message":"success"}
             *
             */
            $response = $sendgrid->web->send($mail);

            if($response === FALSE) {
                //problem with curl transport 
                $message = " Error communicating with sendgrid mail endpoint" ;
                Logger::getInstance()->error($message);
                return self::CURL_ERROR;
            }

            //parse response json
            $responseObj = json_decode($response);
            if(!is_object($responseObj) || !property_exists($responseObj,"message")) {
                //bad json from sendgrid 
                $message = sprintf("Sendgrid mail api response :: [[%s]] is malformed",$response) ;
                Logger::getInstance()->error($message);
                return self::MALFORMED_RESPONSE ;
            }

            $message = $responseObj->message ;
            if(strcasecmp($message,"error") == 0 ) {
                //sendgrid returned error.
                //get errors array
                $message = " Sendgrid mail api returned error" ;
                Logger::getInstance()->error($message);
                foreach($responseObj->errors as $error) {
                    Logger::getInstance()->error($error);
                }

                return self::SENDGRID_ERROR ;
            }

            if(strcasecmp($message,"success") == 0 ) {
                //success
                return 0 ;
            }
            
            return self::UNKNOWN_ERROR ;

        }
    }
}
?>
