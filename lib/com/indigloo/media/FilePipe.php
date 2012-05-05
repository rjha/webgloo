<?php

namespace com\indigloo\media {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger;
    
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
			
            $this->mediaData->originalName = basename($abspath) ;
            $this->fileData = file_get_contents($abspath) ;
            $this->mediaData->mime = 'application/octet-stream' ;
			$this->mediaData->size = strlen($this->fileData); ;

            return ;
        }
        
    }

}
?>
