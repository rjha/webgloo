<?php

/**
 * class to do Django style URL routing
 * 
 */

namespace com\indigloo\core {

        class Router {
            
            private $rules ;
                
            function __construct() {
                $this->rules = array();
            }
            
            function __destruct() {
                
            }
        
            function initTable() {
                
            }
            
            function createRule($pattern,$action,$options=NULL) {
            
                $rule = array();
                $rule["pattern"] = $pattern;
                $rule["action"] = $action ;
                
                if(is_null($options)) {
                    $rule["options"] = array();     
                }else {
                    $rule["options"] = $options ;
                }
                
                $this->rules[] = $rule ;
                
            }
            
            function getRoute($path){

                if(empty($path)) {
                    $message = sprintf("Please supply a valid path to match :: got [%s] ", $path);
                    trigger_error($message,E_USER_ERROR);
                }
                    
                $route = NULL ;
                
                if($path == '/') {
                    $route = $this->matchHome($this->rules);      
                } else {
                    $route = $this->match($this->rules,$path);
                }
        
                return $route ;

            }

            private function matchHome($rules) {
                $route = NULL ;
        
                foreach($rules as $rule) {
                    if($rule["pattern"] == '^/$') {
                        $route = $this->createRoute($rule,array());
                    }
                }
        
                return $route ;
                
            }

            private function match($rules,$path) {
                $path = ltrim($path, '/');
                $matches = array();
                $route = NULL ;

                foreach($rules as $rule) {
                    if(preg_match($this->patternize($rule["pattern"]),$path,$matches) != 0 ) {
                        //match happened 
                        $matches = $this->sanitizeMatches($matches);
                        $route = $this->createRoute($rule,$matches);
                    }
                }
                
                return $route ;

            }

            private function createRoute($rule,$matches) {
                
                $route = $rule ;
                //add parameters
                $route["params"] = $matches ;
                return $route ;
            }
        
            private function sanitizeMatches($matches){
                //discard the first one 
                if (count($matches) >= 1)
                    $matches = array_splice($matches,1);    

                $unset_next = false;
                
                //group name match will create a string key as well as int key 
                // like match["token"] = soemthing and match[1] = something 
                // remove int key when string key is present for same value
                        
                foreach ($matches as $key => $value) {
                    if (is_string($key)){
                        $unset_next = true;
                    } else if (is_int($key) && $unset_next) {
                        unset($matches[$key]);
                        $unset_next = false;
                    }
                }
                
                return array_merge($matches);

            }

            private function patternize($pattern) {
                //http://www.php.net/manual/en/reference.pcre.pattern.modifiers.php
                //treat pattern as UTF-8
                return '{'.$pattern.'}u' ;
            }
            
            //make sure no trailing spaces after closing php tag
            // otherwise you may get strange and hair pulling stupid parse
            // errors from PHP parser.
            
        }
    }
?>