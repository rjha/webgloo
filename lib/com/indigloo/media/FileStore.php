<?php

namespace com\indigloo\media {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger;
    
    class FileStore {

        
        function __construct() {
        }

        function __destruct() {
        
        }

        static function getHashedName($name) {
            
            $token = $name.date(DATE_RFC822);
            $storeName = substr(md5($token), rand(1, 15), 16).rand(1,4096);

            $extension = NULL ;
            $path_parts = pathinfo($name);
            if(isset($path_parts["extension"]) && !empty($path_parts["extension"])) {
                $extension = $path_parts["extension"];
            }

            if(!empty($extension)) {
                $pext = "" ;

                for($i = 0; $i < strlen($extension) ; $i++ ){
                    $ch = $extension{$i};

                    if(ctype_alnum($ch)) {
                        $pext .= $ch ;
                    } else {
                        break ;
                    }
                }

                //copied from media wiki
                // @see http://www.mediawiki.org/wiki/Manual:$wgFileExtensions
                // @see http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types
                $wgFileExtensions = array( 'png', 'gif', 'jpg', 'jpeg', 'ppt', 'pdf', 
                                            'txt', 'xml', 'xls', 'xlsx', 'csv', 'doc',
                                            'docx', 'odt', 'odc', 'odp');

                //processed extension in allowed list?
                if(in_array($pext,$wgFileExtensions)) {
                    $storeName = $storeName. '.' . $pext;
                }

               
            }

            return $storeName ;
        }

        function persist($prefix,$name,$sBlobData,$headers=array()) {

            $storeName = self::getHashedName($name) ;
            $storeName =  $prefix.$storeName ;
            
            $fp = NULL;
            //system.upload.path has a trailing slash
            $path = Config::getInstance()->get_value('system.upload.path').$storeName;
            
            if(!file_exists(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            
            if(Config::getInstance()->is_debug()){
                Logger::getInstance()->debug(" file name = $name");
                Logger::getInstance()->debug(" storage path is => $path ");
            }
            
            //open file in write mode
            $fp = fopen($path, 'w');
            fwrite($fp, $sBlobData);
            fclose($fp);   
            
            return $storeName;
        }

    }
}

?>
