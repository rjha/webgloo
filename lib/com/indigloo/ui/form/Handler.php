<?php

namespace com\indigloo\ui\form {

    use com\indigloo\Configuration as Config;
    use com\indigloo\Logger as Logger;
    use com\indigloo\Util as Util;

    class Handler {

        private $post;
        private $fname;
        private $ferrors;
        private $fvalues;
        private $translate ;
        
        /*
         *
         * @param translate : signals whether form elements should be run through
         * PHP htmlspecialchars function or not. By default we translate all form
         * elements to gaurd against xss and script attacks.
         * 
        */


        function __construct($fname, $post,$translate=true) {
            $this->fname = $fname;
            $this->post = $post;
            
            //keys are form element names
            // and values are output of form handler
            $this->fvalues = array();
            $this->ferrors = array();
            $this->translate = $translate;
        }

        function addRule($name, $displayName, $rules) {
            if (!isset($this->post) || sizeof($this->post) == 0) {
                trigger_error(' Form handler POST array not set', E_USER_ERROR);
            }

            $value = NULL;

            if (isset($this->post[$name])) {
                $value = trim($this->post[$name]);
                $this->processElement($name, $displayName, $value, $rules);

            } else {
                //this key is not found in post
                // this represents a coding issue, not a form error
                // if the element is on form then you get a key
                $message = sprintf('Form POST data does not contain element :: %s',$name);
                trigger_error($message, E_USER_ERROR);
            }
        }

        /**
         * @param $name - name of the form token
         * @param sessionToken - value of form token stored in session 
         *
         */
        function checkToken($name,$sessionToken) {
            //missing security token!
            if(!isset($this->post) || !isset($this->post[$name])) {
                $message = "Security token not found! Please save your data and reload this page.";
                array_push($this->ferrors,$message);
                return ;
            }

            //stale security token!
            $formToken = $this->post[$name];
            if(strcmp($formToken,$sessionToken) != 0) {
                $message = "Security token has expired! Please save your data and reload this page.";
                array_push($this->ferrors,$message);
                return ;
            }

        }
        
        function processElement($name, $displayName, $value, $rules) {
            
            foreach ($rules as $ruleName => $ruleCondition) {
                $this->processRule($ruleName, $ruleCondition, $name,$displayName, $value);
            }
            
            if (Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug("Processed form element $name");
            }
            
        }

        function processRule($ruleName, $ruleCondition, $name,$displayName, $value) {

            switch ($ruleName) {
                case 'maxlength' :
                    //if supplied value length exceeds ruleCondition
                    if (strlen($value) > $ruleCondition) {
                        array_push($this->ferrors, $displayName . " exceeds maximum allowed length :: " . $ruleCondition);
                    }
                    break;
                case 'minlength' :
                    //if supplied value length is less than ruleCondition
                    if (strlen($value) < $ruleCondition) {
                        array_push($this->ferrors, $displayName . " is less than minimum required length :: " . $ruleCondition);
                    }
                    break;
                case 'maxval' :
                    //if supplied value length is less than ruleCondition
                    if (intval($value) > $ruleCondition) {
                        array_push($this->ferrors, $displayName . " exceeds allowed value of :: " . $ruleCondition);
                    }
                    break;
                case 'minval' :
                    //if supplied value length is less than ruleCondition
                    if (intval($value) < $ruleCondition) {
                        array_push($this->ferrors, $displayName . " is less than :: " . $ruleCondition);
                    }
                    break;
                case 'required' :
                    if (strlen($value) == 0) {
                        array_push($this->ferrors, $displayName . " is a required field");
                    }
                    break;
                case 'equal':
                    if (strcmp($value, $ruleCondition) != 0) {
                        array_push($this->ferrors, $displayName . " is not equal to :: " . $ruleCondition);
                    }
                    break;
                case 'rawData' :
                    $this->fvalues[$name] = $value ;
                    break ;
                default:
                    break;
            }
        }

        function addError($error) {
            array_push($this->ferrors, $error);
        }

        function getErrors() {
            if ($this->hasErrors() && Config::getInstance()->is_debug()) {
                Logger::getInstance()->debug($this->fname . " :: posted errors ::");
                Logger::getInstance()->dump($this->ferrors);
            }
            return $this->ferrors;
        }

        function getValues() {
            foreach ($this->post as $key => $value) {
                if (!array_key_exists($key, $this->fvalues)) {
                    $this->fvalues[$key] = $this->getSecureHtml($value);
                }
            }

            return $this->fvalues;
        }

        function getDecoded($name) {
            $val = $this->fvalues[$name];
            $val = htmlspecialchars_decode($val, ENT_QUOTES);
            return $val;
        }

        function hasErrors() {
            if (sizeof($this->ferrors) > 0) {
                return true;
            } else {
                return false;
            }
        }

        function push($name, $value) {
            $this->fvalues[$name] = $value;
        }

        public function getSecureHtml($x) {
            // post value can be array for checkboxes  
            // do not process arrays
            if(is_array($x)) { return $x; }

            $x = (Util::tryEmpty($x) || !$this->translate) ? $x : htmlspecialchars($x,ENT_QUOTES, "UTF-8") ;
            return trim($x) ;
        }

    }

}
?>
