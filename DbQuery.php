<?php
namespace SDB;
use PDO;
use SDB\Db;

use SDB\SQueryObject;

class DbQuery {
    protected $whereCompares = ['<', '>', '=', '<=', '>=', '!=','EXISTS'];
    public $result = '';
    protected $select = false;
    protected $where = false;
    protected $having = false;

    private $pdo;

    public function __construct($queryString = null)
    {
        if(!empty($queryString)) $this->result .= $queryString;

        $this->pdo = Db::getInstance()->getConnect();
    }

    public function toTbStr($value)
    {
        return "'" . $value . "'";
    }

    public function getClearedValue($value)
    {
        return reset(explode(' ', trim(strval($value))));
    }

    public function exec($debug = true)
    {

        if ($debug) echo $this->result . " data";
        try {
            $data = $this->pdo->query($this->result . ";");
            $queryData = $data->fetchAll();
            $this->clear();
            $this->result = '';
        }
        catch (\Exception $e){
            echo $e->getMessage();
            return false;
        }

        return $queryData;
    }

    public function save($debug = false)
    {
        if ($debug) echo $this->result . " data";
        $this->clear();
        $object = new SQueryObject( '(' . $this->result . ')');
        $this->result = '';
        return $object;
    }


    private function aggregate(&$array, $aggregateType)
    {
        $operator = DEFAULT_WHERE_COMPARER;
        $compareValue = null;

        $toMergeStr = [];
        foreach ($array as $col => $value) {

            if (is_array($value)) {

                $operator = reset($value);
                $compareValue = end($value);

                $compareValue = is_object($compareValue)
                    ? $compareValue->getQuery()
                    : $this->toTbStr($compareValue);

            } else if (is_object($value))
                $compareValue = $value->getQuery();

            else
                $operator = $this->toTbStr($value);

            if (in_array($operator, $this->whereCompares))
                $toMergeStr[] = $col . ' ' . $operator . ' ' . $compareValue;
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