<?php
require_once "DB.php";
use SDB\Db;

$res = Db::getInstance()
    ->select("photos",["MAX(id)"])
    ->save();


Db::getInstance()
->select("photos",["id"=>"ID"])
->where(["id" => $res ])
->exec(true);