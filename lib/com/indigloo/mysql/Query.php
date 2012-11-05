<?php

namespace com\indigloo\mysql {

    use \com\indigloo\Util as Util;

    class Query {
        
        const SQL_WHERE = "where";
        const SQL_AND = "and";

        private $query ;
        private $amap ;
        private $prefix;
        private $mysqli;
        
        function __construct($mysqli){
            $this->query = '' ;
            $this->amap = array();
            //pagination also depends on this prefix
            $this->prefix = self::SQL_WHERE;
            $this->mysqli = $mysqli;
        }

        function setPrefixAnd() {
            $this->prefix = self::SQL_AND ;
        }

        function setAlias($class,$alias){
            $this->amap[$class] = $alias;
        }
        
        function filter($filters) {
            if(is_null($filters) || empty($filters)){
                return ;
            }

            foreach($filters as $filter) {
                $model = $filter->model;
                $alias = Util::tryArrayKey($this->amap,get_class($model));
                $value = $filter->value ;
                //sanitize input
                $value = $this->mysqli->real_escape_string($value);
                $filter->sanitize($value);
               
                $condition = $model->filter($filter,$alias);
                $this->addCondition($condition);
            }
        }
        
        function addCondition($sql) {
            $this->query = sprintf(" %s %s %s ",$this->query,$this->prefix,$sql);
            $this->prefix = self::SQL_AND ;
        }

        function getPagination($start,$direction,$key,$limit){
            //and _key [LT|GT] _start order by _key [DESC|ASC] _limit pagesize
            $sql = " %s %s %s %d order by %s %s LIMIT %d ";

            if($direction == 'after') {
                $operator = "<" ;
                $sort = "DESC" ; 
            } else if($direction == 'before'){
                $operator = ">" ;
                $sort = "ASC" ; 
            } else {
                trigger_error("Unknow sort direction in pagination query", E_USER_ERROR);
            }

            $sql = sprintf($sql,$this->prefix,$key,$operator,$start,$key,$sort,$limit);
            return $sql;
        }

        function get() {
            return $this->query;
        }
    }
}
?>
