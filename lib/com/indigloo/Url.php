<?php

namespace com\indigloo {

    use \com\indigloo\Configuration as Config ;

    /*
     * Class to provide utility functions for URL creation and processing.
     * @imp:
     * when creating URL - user is supposed to provide URL encoded parameters.
     * we never urlencode supplied parameters.
     * when processing URL - we always urldecode the parameters. This is in line with
     * default PHP behavior.
     *
     */
     
    class Url {
        
        static function getComponents() {
            
            return array(
                "host" => $_SERVER["HTTP_HOST"],
                "scheme" => Url::scheme(),
                "base" => self::base(),
                "params" => self::getRequestQueryParams()
            );
            
        }
        
        static function base() {
            $base = sprintf("%s://%s", self::scheme(), $_SERVER["HTTP_HOST"]);
            return $base;
        }
        
        static function scheme() {
            
            $scheme = "http" ;
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                $scheme = "https" ;
            }
            
            return $scheme;
        }
        
        static function current() {
           
            if(!isset($_SERVER['REQUEST_URI'])) {
                trigger_error("No REQUEST_URI key in SERVER GLOBAL", E_USER_ERROR);
            }

            return $_SERVER['REQUEST_URI'];
            
        }

        static function getRemoteIp(){
            return $_SERVER['REMOTE_ADDR'];
        }

        /*
         * accept an array of parameters and add to base $url
         * @param params is key-value of parameters.
         * User should take care of encoding parameters if so desired.
         * @return new URL
         *
         */
        static function addQueryParameters($url, $params, $ignore=NULL) {
            // existing params
            $q = self::getQueryParams($url);
            //params values will replace the one in q
            $q2 = array_merge($q, $params);

            if(!is_null($ignore) && is_array($ignore)) {
                foreach($ignore as $key) {
                    unset($q2[$key]);
                }
            }

            $fragment = \parse_url($url, PHP_URL_FRAGMENT);
            $path = \parse_url($url, PHP_URL_PATH);
            $newUrl = self::createUrl($path, $q2, $fragment);
            return $newUrl;
            
        }

        /*
         * @imp: createUrl() will process the input as-it-is, without
         * any encoding. User should take care of encoding parameters.
         * @param  $params values should be URL encoded
         * @return new URL
         *
         */
        static function createUrl($path, $params, $fragment=NULL) {
            $count = 0;
            if(sizeof($params) > 0) {
                foreach ($params as $name => $value) {
                    $prefix = ($count == 0) ? '?' : '&';
                    $path = $path . $prefix . $name . '=' . $value;
                    $count++;
                }

            }

            $path = empty($fragment) ? $path : $path.'#'.$fragment;
            return $path;
        }

        static function getRequestQueryParams() {
            $url = self::current();
            return self::getQueryParams($url);
        }

        /*
         * @imp : we need to urldecode the  parameter values before returning
         * them to user. This is in line with default PHP $_GET behavior.
         * @return an array of URL parameter key value pairs.
         *
         */
        static function getQueryParams($url) {
            
            $query = \parse_url($url, PHP_URL_QUERY);
            $params = array();
            if (empty($query)) {
                return $params;
            } else {
                // PHP parse_url will return the part after ?
                // for /q?arg1=v1&arg2=v2, we will get arg1=1v1&arg2=v2
                $q = explode("&", $query);
                foreach ($q as $kvp) {
                    //break on = to get name value pairs
                    $tokens = explode("=",$kvp);
                    if(isset($tokens[0]) && isset($tokens[1]) && !empty($tokens[0])) {
                        $params[$tokens[0]] = urldecode($tokens[1]);
                    }
                }
            }

            return $params;
            
        }

        /*
         * @return  value of parameter $name from  _GET
         * @imp: As per the manual $_GET auto urldecodes the values
         *
         * @return urldecode value of parameter $name or NULL
         *
         */
        static function tryQueryParam($name){
            $value = NULL ;
            //beware of empty checks - do not use zero etc.
            if(array_key_exists($name,$_GET) && !empty($_GET[$name])){
                $value = $_GET[$name];
            }

            return $value ;
        }

        static function tryBase64QueryParam($name,$default=NULL) {
            $value = self::tryQueryParam($name);
            if(is_null($value) && !is_null($default)) {
                $value = base64_encode($default);
            }

            return $value ;
        }

        static function tryFormUrl($key) {
            $fUrl = isset($_POST[$key]) ? $_POST[$key] : "/site/error/500.php" ;
            return $fUrl;
        }

        /*
         *
         * @return urldecode value of parameter $name
         * @throws error when $name is not part of $_GET
         *
         */

        static function getQueryParam($name){
            $value = NULL ;
            if(array_key_exists($name,$_GET) && !empty($_GET[$name])){
                $value = $_GET[$name];
            }

            if(is_null($value)){
                trigger_error("Required request parameter $name is missing",E_USER_ERROR);
            }

            return $value ;
        }

        static function tryQueryPart($url) {
            $qpart = NULL ;
            $pos = strpos($url, '?');

            if($pos !== false) {
                $qpart = substr($url, $pos+1);
            }

            return $qpart;
        }

        static function addHttp($link) {
            $scheme = \parse_url($link, PHP_URL_SCHEME);
            $link = empty($scheme) ? "http://".$link : $link ;
            return $link ;
        }
    }

}
?>
