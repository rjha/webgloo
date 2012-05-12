<?php


namespace com\indigloo\media {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger;
    use com\indigloo\Util;
    
    class ImageUpload  {

        private $pipe ;
        private $store ;
        private $mediaData ;
        private $errors ;
        private $isS3Store ;
        
        function __construct($pipe) {
            $this->pipe = $pipe ;
            if(Config::getInstance()->get_value("file.store") == 's3'){
                $this->store = new \com\indigloo\media\S3Store() ;
                $this->isS3Store = true ;
            } else {
                $this->store = new \com\indigloo\media\FileStore() ;
                $this->isS3Store = false ;
            }

            $this->errors = array() ;
            $this->mediaData = NULL ;
        }

        function __destruct() {
            
        }
        
        public function getMediaData() {
            return $this->mediaData;
        }
        
        public function getErrors() {
            return $this->errors;
        }
        
        public function process($prefix,$fieldName) {
            $this->pipe->process($fieldName);
            $this->errors = $this->pipe->getErrors();

            if(sizeof($this->errors) > 0 ){
                //set errors and return 
                return ;
            }

            //get meta data and actual file data 
            $this->mediaData = $this->pipe->getMediaData();
            $sBlobData = $this->pipe->getFileData();

            if (is_null($sBlobData)) {
                trigger_error('File processing returned Null Data', E_USER_ERROR);
            }
             
            //do image specific processing here
            $tBlobData = $this->computeHW($sBlobData);

            //caching headers
            $offset = 3600*24*365;
            $expiresOn = gmdate('D, d M Y H:i:s \G\M\T', time() + $offset);
            $headers = array('Expires' => $expiresOn, 'Cache-Control' => 'public, max-age=31536000');

            $storeName = $this->store->persist($prefix,$this->mediaData->originalName,$sBlobData,$headers);


            //create a thumbnail 
            //set correct headers
            $headers["Content-Type"] =  "image/jpeg";
            //change name to jpeg for thumbnail
            //all thumbnails are image type jpeg
            $tname = Util::getThumbnailName($this->mediaData->originalName);
            $thumbnail = $this->store->persist($prefix,$tname,$tBlobData,$headers);
            
            if(is_null($storeName)) {
                array_push($this->errors, "file storage failed");
                return ;
            }

            $this->mediaData->storeName = $storeName;
            $this->mediaData->thumbnail = $thumbnail;
            $this->mediaData->thumbnailName = $tname;

            if($this->isS3Store) {
                $this->mediaData->store = 's3';
                $this->mediaData->bucket = Config::getInstance()->get_value("aws.bucket"); 
            } else {
                $this->mediaData->store = 'local';
                //relative URL for local uploads
                $this->mediaData->bucket = 'media'; 
            }

        }

               
        public function computeHW($sBlobData) {
            //compute height and width using GD2 functions
            // GD2 functions are in global namespace
            $oSourceImage = \imagecreatefromstring($sBlobData);
            if ($oSourceImage == false) {
                //unrecoverable error
                $errorMsg = "GD2 : Not able to create source image from supplied file data ";
                trigger_error($errorMsg, E_USER_ERROR);
            }
            
            //original width and height
            $this->mediaData->width = imagesx($oSourceImage);
            $this->mediaData->height = imagesy($oSourceImage);

            //@todo thumbail width from config
            $td = Util::foldX($this->mediaData->width,$this->mediaData->height,190);
            $oDestinationImage = \imagecreatetruecolor($td["width"], $td["height"]);

            // resample the image - do not use resized if you need quality
            \imagecopyresampled($oDestinationImage,
                $oSourceImage, 0, 0, 0, 0,
                $td["width"], $td["height"],
                $this->mediaData->width, $this->mediaData->height);

            ob_start();
            //default quality is 75. 
            \imagejpeg($oDestinationImage, NULL, 100);
            $tBlobData = ob_get_contents();
            ob_end_clean();
            // Free up memory
            \imagedestroy($oDestinationImage);
            return $tBlobData;   
        }

        
    }
}

?>
