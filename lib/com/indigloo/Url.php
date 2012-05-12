<?php

namespace com\indigloo {

    use com\indigloo\Util ;

    /*
     * @todo whether to urlencode every part or not 
     */
    class Url {

        static function base () {
            return 'http://'.$_SERVER["HTTP_HOST"] ;
                
        }
            
        /*
         * accept an array of param and values and add to
         * this base URI.
         * @param params should be urlencoded
         * @return new URL
         *
         */
        static function addQueryParameters($url, $params,$ignore=NULL) {
            //existing params
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
         * any encoding. 
         * @return new URL
         * 
         */
        static function createUrl($path, $params, $fragment=NULL) {
            $count = 0;

            foreach ($params as $name => $value) {
                $prefix = ($count == 0) ? '?' : '&';
                $path = $path . $prefix . $name . '=' . $value;
                $count++;
            }
            if (!empty($fragment)) {
                $path = $path.'#'.$fragment;
            }
            return $path;
        }
        
        /*
         * @imp : parse_url will urlencode parameters so the returned
         * array has urlencoded elements 
         * @return an array containing key value pairs.
         */
        static function getQueryParams($url) {
            $query = \parse_url($url, PHP_URL_QUERY);
            $params = array();
            if (empty($query)) {
                return $params;
            } else {
				//PHP parse_url will return the part after ?
				// for /q?arg1=v1&arg2=v2, we will get arg1=1v1&arg2=v2
                $q = explode("&", $query);
                foreach ($q as $token) {
                    //break on = to get name value pairs
                    list($name, $value) = explode("=", $token);
                    $params[$name] = $value;
                }
            }

            return $params;
        }

        /* 
         * @return  raw _GET value that is not URL encoded 
         */
		static function tryQueryParam($name){
			$value = NULL ;
			if(array_key_exists($name,$_GET) && !empty($_GET[$name])){
				$value = $_GET[$name];
			}

			return $value ;
		}

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

    }

}
?>
