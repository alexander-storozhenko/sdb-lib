<?php
namespace SDB;
use PDO;

require_once "SQueryObject.php";

define('DEFAULT_JOIN_TYPE', 'INNER');
define('DEFAULT_WHERE_COMPARER', '=');
define('SDB_DEFAULT', 'DEFAULT');

final class Db
{
    protected $whereCompares = ['<', '>', '=', '<=', '>=', '!='];

    protected static $_instance;

    protected $pdo;

    protected $where = false;
    protected $order = false;
    protected $limit = false;
    protected $groupBy = false;
    protected $having = false;
    protected $select = false;
    protected $update = false;
    protected $insert = false;
    protected $delete = false;
    protected $join = false;
    protected $create = false;

    protected $result = '';

    protected $and = 0;
    protected $or = 0;

    protected $tbName = '';

    protected function __construct()
    {
        $db_settings = include('settings.php');
        $this->pdo = new PDO($db_settings['dsn'], $db_settings['user'], $db_settings['pass'], $db_settings['opt']);

    }

    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function toTbStr($value)
    {
        return "'" . $value . "'";
    }

    public function getClearedValue($value)
    {
        return reset(explode(' ', trim(strval($value))));
    }


    public function exec($debug = false)
    {
        if ($debug) echo $this->result . " data";

        $data = $this->pdo->query($this->result);
        $queryData = $data->fetchAll();
        $this->clear();
        $this->result = '';
        var_dump($queryData);
        return $queryData;
    }

    public function buildQuery($debug = false)
    {
        if ($debug) echo $this->result . " data";
        $this->clear();
        $object = new SQueryObject($this->result);
        $this->result = '';
        return $object;
    }


    private function aggregate(&$array, $aggregateType)
    {
        $operator = null;
        $compareValue = null;

        $toMergeStr = [];
        foreach ($array as $col => $value) {

            if (is_array($value)) {

                $operator = reset($value);
                $compareValue = end($value);

                if (is_object($compareValue)) {
                    $compareValue = $compareValue->getQuery();
                }

            } else if ((is_array($value) && count($value) == 1) || !is_array($value)) {

                $operator = DEFAULT_WHERE_COMPARER;

                if (is_object($value)) {
                    $compareValue = $value->getQuery();
                } else
                    $compareValue = $value;

            }

            if (in_array($operator, $this->whereCompares)) {
                $toMergeStr[] = $col . ' ' . $operator . ' ' . $this->toTbStr($compareValue);
            }
        }
        $this->result .= implode(' ' . $aggregateType . ' ', $toMergeStr);
    }

    public function select($table, array $select = ['*'])
    {
        $this->result .= "SELECT ";

        $cols = [];
        foreach ($select as $col => $alias)
            $cols [] = is_numeric($col) ? $alias : $col . ' as ' . $alias;

        $this->result .= implode(', ', $cols);

        $this->result .= ' FROM ';

        $this->result .= $this->getClearedValue($table) . ' ';

        return $this;

    }

    public function insert($table, array $values)
    {
        $this->result .= " INSERT INTO " . $table . " VALUES " . "(" . implode(', ', $values) . ")";
        return $this;
    }

    public function update($table, array $values)
    {
        array_walk($values, [$this, 'toTbStr']);
        $this->result .= " UPDATE " . $table . " SET ";
        foreach ($values as $col => $value)
            $this->result .= $col . ' = ' . $value;
        return $this;
    }

    public function delete($table)
    {
        $this->result .= " DELETE FROM " . $this->getClearedValue($table) . ' ';
        return $this;
    }


    public function where(array $array, $type = 'AND')
    {
        if (!$this->where) {
            $this->result .= "WHERE ";
            $this->where = true;
        }

        $this->aggregate($array, $type);
        return $this;
    }

    public function order($value, $type = "ASC")
    {
        $this->result .= " ORDER BY " . $value . ' ' . $type;
        return $this;
    }

    public function group($values)
    {
        $this->result .= " GROUP BY " . implode(',', $values) . ' ';

        return $this;
    }

    public function join($condition, $table, $type = DEFAULT_JOIN_TYPE)
    {
        $this->result .= ' ' . strtoupper($type) . " JOIN ";
        $this->result .= $table . ' ON ' . $condition . ' ';
        return $this;
    }

    public function having(array $array, $type)
    {
        if (!$this->having) {
            $this->result .= " HAVING ";
            $this->having = true;
        }

        $this->aggregate($array, $type);
        return $this;
    }

    public function limit($limit, $offset)
    {
        $this->result .= " LIMIT " . $limit . " OFFSET " . $offset;
        return $this;
    }

    public function or()
    {
        $this->result .= ' OR ';
        return $this;
    }

    public function and()
    {
        $this->result .= ' AND ';
        return $this;
    }

    public function union($all = false)
    {
        $this->result .= $all ? " UNION ALL " : " UNION ";
        $this->clear();
        return $this;
    }

    public function create(array $array)
    {
        $this->create = array_merge($this->create, $array);
        return $this;
    }

    private function clear()
    {
        $this->select = false;
        $this->update = false;
        $this->having = false;
        $this->insert = false;
        $this->where = false;
        $this->order = false;
        $this->groupBy = false;
        $this->join = false;
        $this->limit = false;
        $this->or = 0;
        $this->and = 0;
    }
}
