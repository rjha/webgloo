<?php

function send_mail($message) {

    $flag = \com\indigloo\Configuration::getInstance()->get_value("send.error.email");
    if(intval($flag) != 1 ) {
        return ;
    }

    $to = \com\indigloo\Configuration::getInstance()->get_value("error.email.to");
    if(empty($to)) {
        return ;
    }

    $from = " support@3mik.com" ;
    $tos = explode(",",$to);
    $node = \com\indigloo\Configuration::getInstance()->get_value("node.name");

    $fromName = "3mik.com error reporter";
    $subject =" Error on 3mik node - ".$node ;
    $text = $message ;
    $html = $message ;
    \com\indigloo\mail\SendGrid::sendViaWeb($tos,$from,$fromName,$subject,$text,$html);
}

function webgloo_error_handler($errno,$errstr,$file,$line) {

    // do nothing for silenced errors
    if(error_reporting() == 0 ) { return true ; }

    switch($errno) {
        case E_STRICT :
        case E_NOTICE :
        case E_USER_NOTICE :
            $message = sprintf("%s:%d [%d]  __ERROR__ %s",$file,$line,$errno,$errstr);
            \com\indigloo\Logger::getInstance()->error($message);
            break ;

        // E_USER_ERROR may not necessarily be a well defined error
        // Hence E_USER_ERRORS should not bubble back to the user
        case E_USER_ERROR:
        default:
            throw new ErrorException($errstr, 0, $errno, $file, $line);
    }
}

function webgloo_exception_handler($ex) {
    $message = $ex->getMessage();
    $message = sprintf("%s:%d  __EXCEPTION__ %s",$ex->getFile(),$ex->getLine(),$message);
    \com\indigloo\Logger::getInstance()->error($message);
    \com\indigloo\Logger::getInstance()->backtrace($ex->getTrace());

    $errorPageUrl = "/error.php?message=system_exception" ;
    header('Location: '.$errorPageUrl);
    send_mail($message);
    exit(1) ;
}

function offline_exception_handler($ex) {
    $message = $ex->getMessage();
    $message = sprintf("%s:%d  __OFFLINE__ %s \n",$ex->getFile(),$ex->getLine(),$message);
    \com\indigloo\Logger::getInstance()->error($message);
    \com\indigloo\Logger::getInstance()->backtrace($ex->getTrace());
    send_mail($message);
    exit(1) ;
}

function webgloo_ajax_exception_handler($ex) {
    $message = $ex->getMessage();
    $message = sprintf("%s:%d  __EXCEPTION__ %s \n",$ex->getFile(),$ex->getLine(),$message);
    \com\indigloo\Logger::getInstance()->error($message);
    \com\indigloo\Logger::getInstance()->backtrace($ex->getTrace());
    
    //send ajax error message back to UI
    $message =  "The server encountered an error. Please try after some time!";
    $error = array('code' => 500, 'message' => $message);
    $message =  json_encode($error) ;
    echo $message ;
    send_mail($message);
    exit(1) ;
}

// set default error handler. Without error_types mask our error_handler will be called 
// for every error regardless of the error_reporting setting
// mixed set_error_handler($callable, int error_types)

ob_start();
set_error_handler('webgloo_error_handler');
set_exception_handler('webgloo_exception_handler');

?>
