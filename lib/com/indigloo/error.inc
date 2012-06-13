<?php

function send_mail($message) {
    $tos = array("jha.rajeev@gmail.com", "srisaurabh2000@gmail.com");
    $from = " support@3mik.com" ;
    $fromName = "3mik.com error reporter";
    $subject =" Error on 3mik.com website " ;
    $text = $message ;
    $html = $message ;
    \com\indigloo\mail\SendGrid::sendViaWeb($tos,$from,$fromName,$subject,$text,$html);
}

function webgloo_error_handler($errorno,$errorstr,$file,$line) {

    // do nothing for silenced errors
    if(error_reporting() == 0 ) { return true ; }

    switch($errorno) {

        case E_STRICT :
        case E_NOTICE :
        case E_USER_NOTICE :
            \com\indigloo\Logger::getInstance()->error(" $file :: $line :: $errorstr");
            break ;

        //E_USER_ERROR are not necessarily well defined errors
        //so we cannot just display back those errors
        //Also we cannot assume that loggin has been done.
        //so we just let it be the default error case!
        case E_USER_ERROR:
        default:
            \com\indigloo\Logger::getInstance()->error($errorstr);
            \com\indigloo\Logger::getInstance()->backtrace();
            //send generic json encoded message back to UI
            $errorPageUrl = "/error.php?message=user_error";
            header('Location: '.$errorPageUrl);
            //Imp:Finally error handler should die
            send_mail($errorstr);
            exit(1);

    }

    //do not execute PHP error handler
    return true ;
}

function webgloo_ajax_error_handler($errorno,$errorstr,$file,$line) {

    // do nothing for silenced errors
    if(error_reporting() == 0 ) { return true ; }

    switch($errorno) {
        case E_STRICT :
        case E_NOTICE :
        case E_USER_NOTICE :
            \com\indigloo\Logger::getInstance()->error(" $file :: $line :: $errorstr");
            break ;

        case E_USER_ERROR:
        default:
            \com\indigloo\Logger::getInstance()->error($errorstr);
            \com\indigloo\Logger::getInstance()->backtrace();

            //send ajax error message back to UI
            $message =  'The server encountered an error. Please try after some time!';
            $error = array('code' => 500, 'message' => $message);
            $message =  json_encode($error) ;
            echo $message ;
            send_mail($errorstr);
            //Imp:Finally error handler should die
            exit(1);
    }

    return true ;
}

function offline_error_handler($errorno,$errorstr,$file,$line) {
    // do nothing for silenced errors
    if(error_reporting() == 0 ) { return true ; }

    switch($errorno) {
        case E_STRICT :
        case E_NOTICE :
        case E_USER_NOTICE :
            \com\indigloo\Logger::getInstance()->error(" $file :: $line :: $errorstr");
            break ;
        default:
            \com\indigloo\Logger::getInstance()->error("__OFFLINE_ERROR__");
            $message = sprintf("file %s - line - %s :: %s \n",$file,$line,$errorstr);
            \com\indigloo\Logger::getInstance()->error($message);
            \com\indigloo\Logger::getInstance()->backtrace();
            send_mail($errorstr);
            exit(1) ;
    }

    //It is important to remember that the standard PHP error handler is completely bypassed
    //unless the callback function returns FALSE
    return true ;
}

function webgloo_exception_handler($ex) {
    $message = $ex->getMessage();
    $message = sprintf("file %s - line - %d :: %s \n",$ex->getFile(),$ex->getLine(),$message);
    \com\indigloo\Logger::getInstance()->error($message);
    \com\indigloo\Logger::getInstance()->backtrace($ex->getTrace());

    $errorPageUrl = "/error.php?message=exception" ;
    header('Location: '.$errorPageUrl);
    send_mail($message);
    exit(1) ;
}

function offline_exception_handler($ex) {
    \com\indigloo\Logger::getInstance()->error("__OFFLINE_ERROR__");
    $message = $ex->getMessage();
    $message = sprintf("file %s - line - %d :: %s \n",$ex->getFile(),$ex->getLine(),$message);
    \com\indigloo\Logger::getInstance()->error($message);
    \com\indigloo\Logger::getInstance()->backtrace($ex->getTrace());
    send_mail($message);
    exit(1) ;
}

//set default error handler
// Without error_types mask our error_handler will be called for every error regardless to
// the error_reporting setting
// mixed set_error_handler($callable, int error_types)

ob_start();
set_error_handler('webgloo_error_handler');
set_exception_handler('webgloo_exception_handler');

?>
