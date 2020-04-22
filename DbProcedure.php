<?php
namespace SDB;
use SDB\DbQuery;

class DbProcedure{



    public  function __construct()
    {
    }

    public function createErrorHandler(){
        $this->addQuery(new \SDB\DbQuery("SET @_rollback=0"));
        $this->addQuery(new \SDB\DbQuery("SET CONTINUE HANDLER FOR SQLEXCEPTION SET `_rollback` = 1;"));

    }

    public function create(){

    }

    public function addQuery(DbQuery $query){
        $this->queries[]= $query;
    }

    public function exec(){
        foreach ($this->queries as $query){

            $res = $query->exec();
            if(!$res)
            {

                return false;
            }
        }
        return true;
    }

}