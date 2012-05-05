<?php

namespace com\indigloo\media {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger;
    
    class S3Store {

        
        function __construct() {
        }

        function __destruct() {
        
        }

        function persist($prefix,$name,$sBlobData,$headers=array()) {

            //create a unique name for s3 store
            $storeName = \com\indigloo\media\FileStore::getHashedName($name) ;
            $storeName =  $prefix.$storeName ;

            $bucket = Config::getInstance()->get_value("aws.bucket");
            $awsKey = Config::getInstance()->get_value("aws.key");
            $awsSecret = Config::getInstance()->get_value("aws.secret");
            
            if(Config::getInstance()->is_debug()){
                Logger::getInstance()->debug(" s3 bucket is => $bucket");
                Logger::getInstance()->debug(" original name => $name");
                Logger::getInstance()->debug(" file path is => $storeName ");
            }


            $s3 = new \S3($awsKey, $awsSecret,false);

            $metaHeaders = array();
            
            //$input, $bucket, $uri, $acl , $metaHeaders, $requestHeaders
            $s3->putObject($sBlobData, $bucket, $storeName, \S3::ACL_PUBLIC_READ, $metaHeaders, $headers);
            return $storeName;

        }

    }
}

?>
