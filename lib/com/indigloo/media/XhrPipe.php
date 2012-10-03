<?php

namespace com\indigloo\media {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger;
    
    /*
     * class to read file data and mime from xmlHttp file uploads.
     * (done via ajax scripts or file upload plugin)
     * This pipe is useful when we want to upload files via ajax and  ImageUpload class.
     *
     *
     */

    class XhrPipe {
        
        private $errors;
        private $fileData;
        private $mediaData ;

        function __construct() {
            $this->errors = array();
            $this->fileData = NULL;
            $this->mediaData = new \com\indigloo\media\Data();
        }

        function __destruct() {
            
        }

        public function getErrors() {
            return $this->errors;
        }

        public function getMediaData() {
            return $this->mediaData;
        }
        
        public function getFileData() {
            return $this->fileData;
        }
        
        private function addError($error) {
            array_push($this->errors,$error) ;
        }
        
        public function process($originalName) {
            $fname = (strlen($originalName) > 255) ? md5($originalName) : $originalName ;
            $this->mediaData->originalName = $fname ;
            $this->fileData = file_get_contents('php://input') ;

            //get mime using finfo.
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $this->fileData);
            $this->mediaData->mime = ($mime === FALSE ) ?  "application/octet-stream" : $mime ;
            
            $this->mediaData->size = strlen($this->fileData); ;
            return ;
        }
        
    }

}
?>