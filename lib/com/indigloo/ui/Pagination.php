<?php

namespace com\indigloo\ui {

    Use \com\indigloo\Url as Url ;

    class Pagination {

        private $pageNo ;
        private $totalPages ;
        private $qparams ;
        private $pageSize ;

        function __construct($qparams,$total,$pageSize) {

            if(array_key_exists('gpa',$qparams) && array_key_exists('gpb',$qparams)){
                trigger_error('Query params has both gpa and gpb variables',E_USER_ERROR);
            }

            // no global varibale page? assume 1 for scrolling
            $this->pageNo = (array_key_exists('gpage',$qparams)) ? $qparams['gpage'] : 1 ;
            $this->totalPages = ceil($total / $pageSize);

            if(empty($this->pageNo) || ($this->pageNo <= 0) || ($this->pageNo > $this->totalPages)) {
                $this->pageNo = 1 ;
            }

            $this->qparams = $qparams ;
            $this->pageSize = $pageSize ;

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

            if(isset($this->qparams) && isset($this->qparams['gpa'])) {
                $direction = 'after' ;
                $start = $this->qparams['gpa'] ;
            }

            if(isset($this->qparams) && isset($this->qparams['gpb'])) {
                $direction = 'before' ;
                $start = $this->qparams['gpb'] ;
            }

            if(empty($start) || empty($direction)) {
                trigger_error('paginator is missing [start | direction ] parameter', E_USER_ERROR);
            }

            $start = base_convert($start,36,10);
            return array('start' => $start , 'direction' => $direction);

        }

        function hasNext() {
            if(($this->pageNo < $this->totalPages) && ($this->pageNo <= 20)) {
                return true ;
            } else {
                return false ;
            }
        }

        function nextPage() {
            return $this->pageNo + 1 ;
        }

        function hasPrevious() {
            if($this->pageNo > 1 ) {
                return true ;
            }else {
                return false ;
            }

        }

        function previousPage() {
            return $this->pageNo - 1 ;
        }

        function render($homeURI,$startId,$endId) {

            if(empty($startId) && empty($endId)) {
                return '' ;
            }

            printf("<ul class=\"pager\">");

            if($this->hasPrevious()){

                $startId = base_convert($startId,10,36) ;
                $bparams = array('gpb' => $startId, 'gpage' => $this->previousPage());
                $q = array_merge($this->qparams,$bparams);
                $ignore = array('gpa');

                $previousURI = Url::addQueryParameters($homeURI,$q,$ignore);
                printf("<li> <a rel=\"prev\" href=\"%s\">&larr; Previous</a> </li>",$previousURI);
            }

            if($this->hasNext()){
                $endId = base_convert($endId,10,36) ;
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
