<?php
namespace Craft\Database\QueryBuilder;

use Craft\Database\Interfaces\BuilderInterface;

abstract class BaseBuilder implements BuilderInterface {
    protected $adapter;
    protected $table;
    protected $columns = ['*'];
    protected $wheres = [];
    protected $bindings = [];

    public function __construct($adapter, string $table)
    {
        $this->adapter = $adapter;
        $this->table = $table;
    }

    public function table(string $table): BuilderInterface {
        $this->table = $table;
        return $this;
    }

    public function select($columns = ['*']): BuilderInterface {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where($column, $operator = null, $value = null): BuilderInterface {
        $this->wheres[] = [$column, $operator, $value];
        $this->bindings[] = $value;
        return $this;
    }

    public function getBindings(): array {
        return $this->bindings;
    }

    /**
     * Execute the current statement and return all records
     */
    public function fetchAll(): array
    {
        $sql = $this->toSql();
        $params = $this->getBindings();
        $result = $this->adapter->query($sql, $params);
        return $this->adapter->fetchAll($result);
    }

    /**
     * fetch() alias for convenience
     */
    public function fetch(string $type = 'assoc')
    {
        $sql = $this->toSql();
        $params = $this->getBindings();
        $result = $this->adapter->query($sql, $params);
        return $this->adapter->fetch($result, $type);
    }

    /**
     * fetch() alias for convenience getting the first record only
     */
    public function first(string $type = 'assoc')
    {
        $rows = $this->fetchAll();
        return $rows[0] ?? null;
    }

    /**
     * fetchAll alias
     */
    public function get(): array
    {
        return $this->fetchAll();
    }

    /**
     * Execute INSERT and return the created ID (if supported)
     */
    public function insertGetId(array $data)
    {
        $sql = $this->insert($data);
        $params = array_values($data);
        $this->adapter->query($sql, $params);
        if (method_exists($this->adapter, 'lastInsertId')) {
            return $this->adapter->lastInsertId();
        }
        return null;
    }

    /**
     * Execute UPDATE, return true if successful
     */
    public function executeUpdate(array $data)
    {
        $sql = $this->update($data);
        $params = array_values($data);
        $params = array_merge($params, $this->getBindings());
        return $this->adapter->query($sql, $params) ? true : false;
    }

    /**
     * Thực thi DELETE, trả về true nếu thành công
     */
    public function executeDelete()
    {
        $sql = $this->delete();
        $params = $this->getBindings();
        return $this->adapter->query($sql, $params) ? true : false;
    }
}