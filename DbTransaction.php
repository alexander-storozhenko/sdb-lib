<?php

namespace SDB;

final class DbTransaction
{
    protected $queries = [];

    public function __construct()
    {
    }

    public function addQuery(DbQuery $query, $savePoint = null)
    {
        $this->queries[] = [$query, $savePoint];

        return $this;
    }

    public function exec()
    {
        foreach ($this->queries as $query) {

            $res = reset($query)->exec();
            $rollback = end($query) === true ? '' : end($query);

            if (!$res) {
                if (count($query) > 1 && !is_null($rollback)) {
                    $this->getRollback($rollback)->exec();
                    exit;
                }
            }

        }
        return true;
    }

    public function startTransaction($type = '')
    {
        Db::getInstance()->beginTransaction();
        return $this;
    }

    private function getRollback($savePoint)
    {
        return empty($savePoint)
            ? new DbQuery("ROLLBACK;")
            : new DbQuery("ROLLBACK TO SAVEPOINT $savePoint;");

    }

//    public function rollback($savePoint)
//    {
//        $this->queries[] = empty($savePoint)
//            ? new DbQuery("ROLLBACK;")
//            : new DbQuery("ROLLBACK TO SAVEPOINT $savePoint;");
//        return $this;
//    }

    public function commit()
    {
        $this->addQuery(new DbQuery("COMMIT;"));

        return $this;
    }

//    public function savePoint($name)
//    {
//        $this->queries[] = " SAVEPOINT $name; ";
//        return $this;
//    }

}
