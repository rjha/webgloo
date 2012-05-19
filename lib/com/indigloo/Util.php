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

        static function filterAscii($input) {
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
			if(array_key_exists($name,$arr) && !empty($arr[$name])){
				$value = $arr[$name];
			}

			return $value ;
		}

		static function getArrayKey($arr,$name){
			$value = NULL ;
			if(array_key_exists($name,$arr) && !empty($arr[$name])){
				$value = $arr[$name];
			}

			if(is_null($value)){
				trigger_error("Required array key $name is missing",E_USER_ERROR);
			}

			return $value ;
		}

        /*
         * @param json string coming from database
         * escape single quotes in json string 
         *
         */
        static function formSafeJson($json) {
            $json = empty($json) ? '[]' : $json ;
            $json = str_replace("'","&#039;",$json);
            return $json;
        }

        static function unsetInArray(&$data, $keys) {
            foreach($keys as $key){
                if(isset($data[$key]))
                    unset($data[$key]);
            }

            foreach ($data as &$element) {
                if (is_array($element)) {
                    self::unsetInArray($element, $keys);
                }
            }
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

    }

}
?>
