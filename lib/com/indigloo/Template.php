<?php

namespace com\indigloo {


    class Template {

        static function compile($content) {
            $open = false ;
            $buffer = '' ;
            $i = 0 ; 

            while($i < strlen($content)){
                $ch = $content[$i] ;

                if($ch == '~' ) { 
                    if($open) {
                        //close now
                        $buffer .= ' ?>' ;
                        $open = !$open ;
                    }else {
                        //not open
                        if($content[$i+1] == '~' ) { 
                            //double tilde case
                            $buffer .= '<?php echo ';
                            //advance pointer
                            $i++ ;
                        }else {
                            //single tilde
                            $buffer .= '<?php ' ;
                        }   
                        //change state now
                        $open = !$open ;
                    }   
                } else {
                    $buffer .= $ch ;
                }   

                $i++;
            } //loop

            return $buffer ;
        }

        /*
         * @param tfile path relative to APP_WEB_DIR
         *
         */
        static function render($tfile, $view) {
    
            $html = NULL ;

            if(empty($view)){
                $message = "No view defined for template rendering" ;
                trigger_error($message,E_USER_ERROR);
            }


            //full path of template file
            $ftemplate = APP_WEB_DIR.$tfile ;
            $fcompiled = APP_WEB_DIR.'/compiled'.$tfile . ".cc" ;

            
            if(!file_exists($fcompiled) || (filemtime($fcompiled) < filemtime($ftemplate))) {

                //create compile directory
                if(!file_exists(dirname($fcompiled))) {
                    mkdir(dirname($fcompiled), 0755, true);
                }

                $content = file_get_contents($ftemplate);
                $fp = fopen($fcompiled,"w");
                $code = self::compile($content);
                //write code to .cc file 
                fwrite($fp,$code);
                fclose($fp);
            }
            
            //execute compiled php code
            ob_start();
            include($fcompiled);
            $html = ob_get_contents();
            ob_end_clean(); 
            return $html ;
    
        }
        
    }

}

?>
