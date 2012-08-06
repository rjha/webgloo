<?php

namespace com\indigloo {


    use \com\indigloo\Configuration as Config;

    class Util {

        static function base64Encrypt($token) {
            $token = base64_encode($token);
            $token = str_rot13($token);
            return $token;
        }

        static function base64Decrypt($token) {
            $token = str_rot13($token);
            $token = base64_decode($token);
            return $token;
        }

        static function getBase36GUID() {
            $baseId = rand();
            $token = base_convert($baseId * rand(), 10, 36);
            return $token;
        }

        static function getMD5GUID() {
            $token = md5(uniqid(mt_rand(), true));
            return $token;
        }

        function getRandomString($length = 8) {
            $characters = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $string = '';

            for ($i = 0; $i < $length; $i++) {
                $string .= $characters[mt_rand(0, strlen($characters) - 1)];
            }

            return $string;
        }

        static function array2nl($arr) {

            $str = array_reduce($arr, create_function('$a,$b', 'return $a."\n".$b ;'));
            return $str;
        }

        /**
         *
         * @param <type> $original - timestamp coming from mysql DB
         * @param <type> $format   - output format , defaults to dd mon yyyy
         * @return <type> the formatted date string
         *
         * @see also http://in2.php.net/strftime
         * @see also http://in2.php.net/manual/en/function.strtotime.php
         * PHP string time functions
         *
         */
        static function formatDBTime($original, $format="%d-%b, %Y / %H:%M") {

             if (!isset($original) || empty($original)) {
                trigger_error("Wrong input: empty or null timestamp",E_USER_ERROR);
            }

            $dt = strftime($format, strtotime($original));
            return $dt;
        }

        static function secondsInDBTimeFromNow($original) {

            if (!isset($original) || empty($original)) {
                trigger_error("Wrong input: empty or null timestamp",E_USER_ERROR);
            }

            //calculate base time stamp
            $basets = strtotime("now");
            $ts = strtotime($original);
            $interval = $ts - $basets;
            return $interval;
        }

        static function squeeze($input) {
            $input = preg_replace('/\s\s+/', ' ', $input);
            return $input;
        }

        /*
         * if you are not bothered about words breaking in the middle
         * then use php  substr. abbreviate is good for preserving "proper"
         * words.
         *
         */

        static function abbreviate($input,$width) {
            if(empty($input)) return $input ;

            if (strlen($input) <= $width) {
                return $input;
            }

            $output = substr($input,0,$width);

            //normals words are seldom more than 30 chars
            $pos = 0 ;
            $found = false ;

            for($i = $width-1 ; $i >= 0 ; $i--) {
                 if(ctype_space($output[$i])) {
                    $found = true ;
                    break ;
                 }
                 $pos++ ;
            }

            if($found && ($pos > 0)) {
                $output = substr($output,0,($width-$pos));
                $output = rtrim($output) ;
            }

            return $output;
        }

        static function isAlphaNumeric($input) {
            //Allow spaces
            $input = preg_replace('/\s+/', '', $input);
            return ctype_alnum($input);
        }

        static function filterBadUtf8($input){
            $clean = iconv('UTF-8', 'UTF-8//IGNORE', $input);
            return $clean;
        }

        static function filterNonAscii($input) {
            //replace non-alpha with space
            //@imp: ASCII specific filtering - will not work for utf-8
            $input = preg_replace("/[^0-9a-zA-Z.]/i", ' ', $input);

            $input = self::squeeze($input);
            $input = str_replace(" ","-",$input);
            return $input ;
        }

        /*
         * used to check empty strings
         * php empty() will return TRUE for "<spaces>" and false
         * for "0". we are interested in user inputs and want to catch
         * empty or all spaces only
         *
         */
        static function isEmpty($name, $value) {
            if(is_null($value)) {
                $message = 'Bad input:: ' . $name . ' is empty or null!';
                trigger_error($message, E_USER_ERROR);
            }

            $value = trim($value);

            if(strlen($value)  == 0 ) {
                $message = 'Bad input:: ' . $name . ' is empty or null!';
                trigger_error($message, E_USER_ERROR);
            }

        }

        static function isEmptyMessage($name, $value) {
            if (self::isEmpty($value)) {
                $message = "Bad input :: $name is empty or null \n";
                echo nl2br($message);
                exit ;
            }
        }

        static function tryEmpty($value) {
            if(is_null($value)) { return true ; }
            $value = trim($value);
            if(strlen($value)  == 0 ) { return  true ; }
            return false ;
        }

        static function startsWith($haystack, $needle) {
            // Recommended version, using strpos
            return strpos($haystack, $needle) === 0;
        }

        static function convertBytesIntoKB($bytes) {
            //divide bytes by 1024
            $kb = ceil(($bytes / 1024.00));
            return $kb;
        }

        /*
         * given a fixed width of container w0, try to fold a width=w, height=h box so that
         * the original aspect ratio is preserved. There is No restriction on height
         *
         */
        static function foldX($w,$h,$w0) {
            if($w > $w0 ) {
                $w2 = $w0 ;
                $h2 = floor(($w0/$w) * $h) ;
                return array("width" => $w2, "height" => $h2);
            } else {
                //return original
                return array("width" => $w, "height" => $h);
            }

        }

        /*
         * given a container with width = w0 and height = h0, try to fit an element
         * of width=w, height=h so that the original (w/h) aspect is preserved.
         * this algorithm will terminate in 2 steps
         *
         */

        static function foldXY($w,$h,$w0,$h0) {

            if(($h <= $h0) && ($w <= $w0)) {
                //terminate
                return array("width" => $w, "height" => $h);
            }

            if($w > $w0 ) {
                $w2 = $w0 ;
                $h2 = floor(($w0/$w)*$h) ;
                return self::foldXY($w2,$h2,$w0,$h0) ;
            }

            if($h > $h0 ) {
                $h2 = $h0 ;
                $w2 = floor(($h0/$h)*$w);
                return self::foldXY($w2,$h2,$w0,$h0) ;
            }
        }

        static function tryArrayKey($arr,$name){
            $value = NULL ;
            if(isset($arr[$name])){
                //$value can be any type - e.g. array, object
                // do not use type specific function like trim() w/o type check
                $value = $arr[$name];
            }

            return $value ;
        }

        static function getArrayKey($arr,$name){
            $value = NULL ;
            if(isset($arr[$name])){
                //$value can be any type - e.g. array, object
                // do not use type specific function like trim() w/o type check
                $value = $arr[$name];
            } else {
                trigger_error("Required array key $name is missing",E_USER_ERROR);
            }

            return $value ;
        }

        /*
         * @param json input string
         * make input string safe to be used inside html and javascript
         * This needs some explaining.
         *
         *  when storing json data
         *  -----------------------
         *
         * we use 
         *
         * 1) javascript stringify() to push json data inside forms
         * 2) PHP scripts to manipulate data already stored in our DB
         *
         * FYI: javascript JSON stringify() will not escape the solidus and is
         * right about escaping. However PHP 5.3 will escape solidus. This escaping of
         * solidus is a problem. PHP 5.4 provides options to unescape slashes
         * but we are on 5.3.
         *
         * so anywhere you are using json_encode on a json object to stringify it via PHP
         * you should worry about "unescaping this extra escaping of solidus", for e.g in PHP DB 
         * scripts that "modify" existing images_json etc.
         * 
         * 3)carriage returns and newlines are escaped as \\r and \\n by PHP json_encode
         *
         *  when loading json from DB in a page via PHP
         *  ---------------------------------------------
         *
         * There are 2 issues
         *
         * 1) control characters like backslash-n interpreted as "literal backslash-n"
         *
         * php json_encode has done the right escaping, say converting a newline
         * character to \\n. However when we put this json_encoded string inside  javascript
         * as a literal, "\\n" is interpreted as a literal "\n" (backslash escaping next backslash)
         * Now Json.parse() will see literal "\n" as newline feed and fail.
         *
         * 2) issue with solidus (slash)
         *
         * for e.g. http://www.3mik.com will be changed to http:\/\/www.3mik.com by PHP json_encode.
         * unfortunately, inside this formSafeJson() function we escape backslash as
         * backslash backslash (to take care of control characters) - that will result in
         * DB string displayed as -  http:\\/\\/www.3mik.com
         *
         * so we need to change the "escaped solidus" before running our filter for
         * control characters.
         *
         *
         * Solution
         * -----------------
         * when storing
         *
         * 1) if json encoded data is sent to DB via java script - solidus is not escaped
         * 2) if json_encoded data is sent to DB via a PHP script
         *   e.g. a script that json_decode post.images_json , does some manipulation and
         *   then json_encodes the string again - will escape solidus in PHP 5.3
         *   This is a problem :- we should remove "escaping of solidus" before storing.
         *
         * when displaying
         *
         * 1) Get the string from DB, run it through this filter and assign to a variable
         *  in javascript inside single quotes.
         *
         *
         *
         * @see http://stackoverflow.com/questions/1048487/phps-json-encode-does-not-escape-all-json-control-characters/
         *
         * @see https://bugs.php.net/bug.php?id=49366 - for solidus escaping bug.
         * @see http://noteslog.com/post/the-solidus-issue/ - for solidus issue.
         *
         */

        static function formSafeJson($json) {
            $json = empty($json) ? '[]' : $json ;
            //remove escaping of solidus done by PHP json_encode
            $json = str_replace("\/","/",$json);

            //now escape json control characters
            $search = array('\\', "\n","\r","\f","\t","\b","'") ;
            $replace = array('\\\\',"\\n", "\\r","\\f","\\t","\\b", "&#039");
            $json = str_replace($search,$replace,$json);

            return $json;
        }

        static function encrypt($text) {
            //max key size 24 for MCRYPT_RIJNDAEL_256
            $key = Config::getInstance()->get_value("tmp.encrypt.key");
            $crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB);
            $crypt = base64_encode($crypt);
            return $crypt;
        }

        static function decrypt($crypt) {
            $key = Config::getInstance()->get_value("tmp.encrypt.key");
            $crypt = base64_decode($crypt);
            $text = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $crypt, MCRYPT_MODE_ECB);
            //trim any extra padding
            $text = trim($text);
            return $text;
        }

        static function getThumbnailName($name) {
            //no name
            if(self::tryEmpty($name)) {
                $name = "no-name-t190.jpg";
                return $name;
            }

            $pos = strrpos($name, '.');
            if ($pos === false) {
                //no extension in original name
                return $name."-t190.jpg" ;
            } else {
                $part = substr($name,0,$pos);
                return $part."-t190.jpg";
            }
        }

        static function getMimeFromName($name) {
            $mime = NULL ;
            $map = array(
                "gif" => "image/gif",
                "jpg" => "image/jpeg",
                "jpeg" => "image/jpeg",
                "png" => "image/png");

            $pos = strrpos($name, '.');
            if ($pos !== false) {
                $extension = substr($name,$pos+1);

                if(($extension !== false) && !empty($extension)) {
                    $extension = strtolower($extension);
                    $mime = (isset($map[$extension])) ? $map[$extension] : NULL ;
                }

            }

            return $mime ;

        }

    }

}
?>
