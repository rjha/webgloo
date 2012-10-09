<?php

/*
 *
 *
 * class to read file data and mime from browsers file uploads.
 * (done with a file upload button).
 * This pipe is useful when we want to upload files via browser and  ImageUpload class.
 *
 *
 * 1) see the following php.ini settings
 *  - post_max_size 
 *  - upload_max_filesize
 * 
 * 2) webserver should have write permission on server TEMP dir 
 * 3) Also, an empty file input box is always set to an array 
 *  so isset($_FILES[$fieldName]) is redundant , this field is never empty 
 *
 *
 */

namespace com\indigloo\media {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger;
    
    class FormPipe {
        
        const ERROR_FIELD_MISSING = " No file found in post! Did you upload a file?";
        const ERROR_INI_SIZE= " file size greater than php.ini upload_max_file ";
        const ERROR_PARTIAL = " partial file received ";
        const ERROR_NO_FILE = " no file selected on form ";
        const ERROR_FILE_SIZE = " file size is greater than : ";
        const ERROR_UNKNOWN = " unknown PHP error during file upload  ";

        private $isRequired;
        private $isEmpty;
        private $errors;
        private $fileData;
        private $mediaData ;

        function __construct() {

            $this->isRequired = true;
            $this->errors = array();
            $this->isEmpty = false;
            $this->fileData = NULL;
            $this->mediaData = new \com\indigloo\media\Data();
        }

        function __destruct() {
            
        }

        public function getErrors() {
            return $this->errors;
        }

        /* do we allow empty file field on form? */
        public function setRequired($flag) {
            $this->isRequired = $flag;
        }

        public function getMediaData() {
            return $this->mediaData;
        }
        
        public function getFileData() {
            return $this->fileData;
        }

        public function isEmpty() {
            return $this->isEmpty;
        }
        
        private function addError($error) {
            array_push($this->errors,$error) ;
        }
        

        public function process($fieldName) {
            
            $maxSize = Config::getInstance()->max_file_size();
            if (empty($maxSize) || is_null($maxSize)) {
                trigger_error('file maxsize is not set in config file',E_USER_ERROR);
            }
            
            if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
                // error when files are required on web form
                if ($this->isRequired) {
                    $this->addError(self::ERROR_FIELD_MISSING);
                    
                } else {
                    $this->isEmpty = true;
                }
                return;
            }

            // form field is set
            $fdata = $_FILES[$fieldName];
            
            /* check for all possible error codes */
            switch ($fdata['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    // image size > php.ini setting
                     $this->addError(self::ERROR_INI_SIZE);
                    break;

                case UPLOAD_ERR_PARTIAL :
                    // partial upload
                    $this->addError(self::ERROR_PARTIAL);
                    break;

                case UPLOAD_ERR_NO_FILE:
                    // no file selected for upload
                    if ($this->isRequired) {
                        $this->addError(self::ERROR_NO_FILE);
                    }

                    break;

                case UPLOAD_ERR_FORM_SIZE:
                    // file too large vis-a-vis hidden form field
                    // Users can fake this one
                    $this->addError(self::ERROR_FILE_SIZE.$maxSize);
                    break;
                
                case UPLOAD_ERR_OK :
                    //check for file data size
                    if ($fdata['size'] > $maxSize) {
                        // file size too large
                        $this->addError(self::ERROR_FILE_SIZE.$maxSize);
                    }
                    break;
                default :
                    // unknown error
                    $this->addError(self::ERROR_UNKNOWN);
            }
    
            if(sizeof($this->errors) > 0 ) {
                return ;
            }

            $fname = basename($fdata['name']) ;
            $this->mediaData->originalName = (strlen($fname) > 255) ? md5($fname) : $fname ;

            $ftmp = $fdata['tmp_name'];
            $oTempFile = fopen($ftmp, "rb");
            
            $size = filesize($ftmp);
            $this->mediaData->size = $size ;
            $this->fileData = fread($oTempFile, $size);

            //get mime using finfo.
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $this->fileData);
            $this->mediaData->mime = ($mime === FALSE ) ?  "application/octet-stream" : $mime ;

            return ;
        }
        
    }

}
?>
