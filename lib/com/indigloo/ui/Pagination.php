<?php

namespace com\indigloo\ui {

    Use \com\indigloo\Url as Url ;

    class Pagination {

        private $pageNo ;
        private $qparams ;
        private $pageSize ;
        private $maxPageNo ;
        private $convert ;

        function __construct($qparams,$pageSize) {
            $this->pageNo = -1 ;
            $this->maxPageNo = -1 ;
            $this->convert = true ;

            if(!empty($qparams) && isset($qparams["gpage"])) {
                $this->pageNo = $qparams["gpage"];
            }else {
                $this->pageNo = 1 ;
            }

            if(array_key_exists("gpa",$qparams) && array_key_exists("gpb",$qparams)){
                // both gpa and gpb param in request
                // we do not know what page you are asking for!
                $this->pageNo = 1 ;

            }

            if(!array_key_exists("gpa",$qparams) && !array_key_exists("gpb",$qparams)){
                // both gpa and gpb param missing
                // we do not know what page you are asking for!
                $this->pageNo = 1 ;
            }

            settype($this->pageNo,"integer");
            if(empty($this->pageNo) || ($this->pageNo <= 0)) {
                $this->pageNo = 1 ;
            }

            $this->qparams = $qparams ;
            $this->pageSize = $pageSize ;

        }

        function setMaxPageNo($max) {
            $this->maxPageNo = $max ;
        }

        function setBaseConvert($flag) {
            $this->convert = $flag ;
        }

        function isHome() {
            $flag = ($this->pageNo == 1 )? true : false ;
            return $flag;
        }

        function getPageNo(){
            return $this->pageNo ;
        }

        function getPageSize() {
            return $this->pageSize ;
        }

        function getDBParams() {

            $start = NULL ;
            $direction = NULL ;

            if(isset($this->qparams) && isset($this->qparams["gpa"])) {
                $direction = "after" ;
                $start = $this->qparams["gpa"] ;
            }

            if(isset($this->qparams) && isset($this->qparams["gpb"])) {
                $direction = "before" ;
                $start = $this->qparams["gpb"] ;
            }

            // both gpa and gpb are missing from request URL
            // during paginator construction - this should be flagged
            // as pageNo == 1 
            if(empty($start) || empty($direction)) {
                trigger_error("paginator is missing [start | direction ] parameter", E_USER_ERROR);
            }

            $start = ($this->convert) ? base_convert($start,36,10) : $start;
            return array("start" => $start , "direction" => $direction);
        }

        function hasNext($gNumRecords) {
            $flag = ($gNumRecords >= $this->pageSize) ? true : false ;
            if($flag && $this->maxPageNo > 1 ) {
                $flag = ($this->pageNo < $this->maxPageNo ) && flag ;
            }

            return $flag ;
        }

        function nextPage() {
            return $this->pageNo + 1 ;
        }

        function hasPrevious() {
            $flag = ($this->pageNo > 1 ) ? true : false ;
            return $flag ;
        }

        function previousPage() {
            return $this->pageNo - 1 ;
        }

        function render($homeURI,$startId,$endId,$gNumRecords) {

            if(empty($startId) && empty($endId)) {
                return "" ;
            }

            printf("<ul class=\"pager\">");

            if($this->hasPrevious()){

                $startId = ($this->convert) ? base_convert($startId,10,36) : $startId ;
                $bparams = array('gpb' => $startId, 'gpage' => $this->previousPage());
                $q = array_merge($this->qparams,$bparams);
                $ignore = array('gpa');

                $previousURI = Url::addQueryParameters($homeURI,$q,$ignore);
                printf("<li> <a rel=\"prev\" href=\"%s\">&larr; Previous</a> </li>",$previousURI);
            }

            if($this->hasNext($gNumRecords)){
                $endId = ($this->convert) ? base_convert($endId,10,36) : $endId ;
                $nparams = array('gpa' => $endId, 'gpage' => $this->nextPage()) ;
                $q = array_merge($this->qparams,$nparams);
                $ignore = array('gpb');

                $nextURI = Url::addQueryParameters($homeURI,$q,$ignore);
                printf("<li> <a rel=\"next\" href=\"%s\">Next &rarr;</a> </li>",$nextURI);
            }

            printf("</ul>");
        }
    }

}
?>
