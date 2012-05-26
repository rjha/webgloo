<?php

    include ('news-app.inc');
    include(APP_WEB_DIR . '/inc/header.inc');
    
    use com\indigloo\Util as Util;
    
     
    $pipe = new \com\indigloo\media\Upload();
    $store = new \com\indigloo\media\FileUpload($pipe);
    $uploader = new com\indigloo\media\ImageUpload($store);
    $uploader->process("Filedata");
    
    $errors = $uploader->getErrors() ;

    if (sizeof($errors) > 0 ) {
        $data = array('code' => 500, 'message' => $errors[0]);
        echo json_encode($data);
    
    } else {
        
        $mediaVO = $uploader->getMediaData();
        $mediaVO->bucket = 'media' ;
        $mediaVO->id  =1234;
           
        $message = 'file upload done!';
        $data = array('code' => 0, 'mediaVO' => $mediaVO, 'message' => $message);
        echo json_encode($data);
    
    }



?>
