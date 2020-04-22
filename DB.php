<?php
namespace SDB;
use PDO;

require_once "SQueryObject.php";

define('DEFAULT_JOIN_TYPE', 'INNER');
define('DEFAULT_WHERE_COMPARER', '=');
define('SDB_DEFAULT', 'DEFAULT');
define('SDB_ROLLBACK_EMPTY', '');

final class Db
{
    protected static $_instance;
    protected $pdo;

    protected function __construct()
    {
        $db_settings = include('settings.php');
        $this->pdo = new PDO($db_settings['dsn'], $db_settings['user'], $db_settings['pass'], $db_settings['opt']);
    }

    public function getConnect()
    {
        return $this->pdo;
    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollback()
    {
        return $this->pdo->rollBack();
    }


}
