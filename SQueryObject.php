<?php
namespace SDB;
class SQueryObject{

    private $query = '';

    public  function __construct($query)
    {
        $this->query = $query;
    }

    public function getQuery(){
        return '(' . $this->query . ')';
    }
}