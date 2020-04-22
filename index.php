<?php
require_once "DB.php";
require_once "DbQuery.php";
require_once "DbProcedure.php";
require_once "DbTransaction.php";

use SDB\Db;
use SDB\DbQuery;
use SDB\DbTransaction;

$photos = (new DbQuery())->insert("photos",[SDB_DEFAULT,'12']);
$photos2 = (new DbQuery())->insert("photos2",[SDB_DEFAULT,'12']);

(new DbTransaction())
    ->startTransaction()
    ->addQuery($photos,null)
    ->addQuery($photos2,true)
    ->commit()
    ->exec();
















