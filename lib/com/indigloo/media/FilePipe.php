<?php

namespace com\indigloo\media {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger;
    
    /**
     * class to read data and mime from files on  local filesystem. 
     * This pipe is useful when we want to upload local files via ImageUpload class.
     * for e.g. if we want to upload local media folder to Amazon S3 bucket.
     *
     * @see \com\indigloo\media\ImageUpload
     *
     *
     */
    class FilePipe {
        
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

        public function process($abspath) {
            
            $fname = basename($abspath) ;
            $this->mediaData->originalName = (strlen($fname) > 255) ? md5($fname) : $fname ;

            $this->fileData = file_get_contents($abspath) ;

            //get mime using finfo.
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $abspath);
            $this->mediaData->mime = ($mime === FALSE ) ?  "application/octet-stream" : $mime ;

            $this->mediaData->size = strlen($this->fileData); ;

            return ;
        }
        
    }

}
?>
