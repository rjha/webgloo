<?php

/**
 *
 * @author rajeevj
 */

namespace com\indigloo\core {


    class Request {

        //parameters are the ones that are already part of REQUEST global variable
        // you cannot reset a request parameter. attributes is what the executing
        // script sets in request scope for housekeeping
        
        private $params;
        private $attribs;

        function __construct() {
            $this->params = array();
            $this->attribs = array();
            //load server _GET and _POST parameters into request
            //request_order directive is set to GP
            foreach($_REQUEST as $key => $value) {
                $this->params[$key] = $value ;
            }
        }

        function getParam($key) {
            if (array_key_exists($key, $this->params)) {
                $value = $this->params[$key];
            } else {
                $value = NULL;
            }
            return $value;
        }

        function getParams() {
            return $this->params;
        }

        function getAttribute($key) {
            if (array_key_exists($key, $this->attribs)) {
                $value = $this->attribs[$key];
            } else {
                $value = NULL;
            }
            return $value;
        }

        function getAttributes() {
            return $this->attribs;
        }

        function setAttribute($key, $value) {
            $this->attribs[$key] = $value;
        }

        function __toString() {
            $buffer = '';
            $buffer .= 'Params: [';
            foreach ($this->params as $key => $value) {
                $buffer.= ' {' . $key . ':' . $value . '} ';
            }
            $buffer .= '] ';

            $buffer .= ' Attributes: ';
            foreach ($this->attribs as $key => $value) {
                $buffer.= ' {' . $key . ':' . $value . '} ';
            }
            $buffer .= '] ';

            return $buffer;
        }

    }

}
?>
