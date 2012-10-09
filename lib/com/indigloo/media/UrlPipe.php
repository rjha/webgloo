<?php

namespace com\indigloo\media {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger;
    
    /**
     * class to read data and mime from a given URL
     * This pipe is useful when we want to upload resources from external 
     * URL supplied by users.
     *
     * @see \com\indigloo\media\ImageUpload
     *
     *
     */
    class UrlPipe {
        
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
        
        public function process($url) {
            
            $fname = basename($url) ;
            $this->mediaData->originalName = (strlen($fname) > 255) ? md5($fname) : $fname ;
            
            $this->fileData = file_get_contents($url) ;

            // get mime using finfo.
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $this->fileData);
            $this->mediaData->mime = ($mime === FALSE ) ?  "application/octet-stream" : $mime ;

            $this->mediaData->size = strlen($this->fileData); ;
            return ;
        }
        
    }

}
?>
