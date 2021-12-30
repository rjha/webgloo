<?php

function get_user_diagnostics() {
    // diagnostic info
    $login = \com\indigloo\sc\auth\Login::tryLoginInSession();
    $name = (is_null($login)) ? "_N_" : sprintf("%s :: %s ",$login->id,$login->name) ;

    $message = "\n + URL = {%s}\n + IP = {%s}\n + agent = {%s}\n + login = {%s} \n";
    $message = sprintf($message,
        $_SERVER['REQUEST_URI'],
        $_SERVER['REMOTE_ADDR'], 
        $_SERVER['HTTP_USER_AGENT'],
        $name);

    \com\indigloo\Logger::getInstance()->error($message);
    return $message ;
}

function webgloo_error_handler($errno,$errstr,$file,$line) {

    // do nothing for silenced errors
    if(error_reporting() == 0 ) { 
        return true ; 
    }
    
    switch($errno) {
        case E_STRICT :
            $message = sprintf("%s:%d [%d]  __STRICT__ %s",$file,$line,$errno,$errstr);
            \com\indigloo\Logger::getInstance()->error($message);
            break ;
        case E_NOTICE :
        case E_USER_NOTICE :
            $message = sprintf("%s:%d [%d]  __NOTICE__ %s",$file,$line,$errno,$errstr);
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
   
    $diagnostic = get_user_diagnostics();
    /* error message */
    $message = $ex->getMessage();
    $message = sprintf("%s:%d  __EXCEPTION__ %s",$ex->getFile(),$ex->getLine(),$message);
    \com\indigloo\Logger::getInstance()->error($message);
    \com\indigloo\Logger::getInstance()->backtrace($ex->getTrace());

    $errorPageUrl = "/site/error/500.php?message=system_exception" ;
    header('Location: '.$errorPageUrl);
    exit(1) ;
}

function offline_exception_handler($ex) {
    
    $message = $ex->getMessage();
    $message = sprintf("%s:%d  __OFFLINE__ %s \n",$ex->getFile(),$ex->getLine(),$message);
    \com\indigloo\Logger::getInstance()->error($message);
    \com\indigloo\Logger::getInstance()->backtrace($ex->getTrace());
    exit(1) ;
    
}

function webgloo_ajax_exception_handler($ex) {
    
    $diagnostic = get_user_diagnostics();
    $message = $ex->getMessage();
    
    $message = sprintf("%s:%d  __EXCEPTION__ %s \n",$ex->getFile(),$ex->getLine(),$message);
    \com\indigloo\Logger::getInstance()->error($message);
    \com\indigloo\Logger::getInstance()->backtrace($ex->getTrace());
    
    //send nice message  to UI
    $ui_message =  "The server encountered an error. Please try after some time!";
    $error = array('code' => 500, 'message' => $ui_message);
    echo json_encode($error) ;
    exit(1) ;
    
}

// set default error handler. Without error_types mask our error_handler will be called 
// for every error regardless of the error_reporting setting
// mixed set_error_handler($callable, int error_types)

ob_start();
set_error_handler('webgloo_error_handler');
set_exception_handler('webgloo_exception_handler');

?>
