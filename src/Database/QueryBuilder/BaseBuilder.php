<?php
namespace Craft\Database\QueryBuilder;

use Craft\Database\Interfaces\BuilderInterface;

abstract class BaseBuilder implements BuilderInterface {
    protected $adapter;
    protected $table;
    protected $columns = ['*'];
    protected $wheres = [];
    protected $bindings = [];
    protected $operation = null; // select|insert|update|delete
    protected $pendingData = [];

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
        $this->operation = 'select';
        return $this;
    }

    public function where($column, $value = null, $operator = "="): BuilderInterface {
        // Support shorthand: where('id', $id) => where('id', '=', $id)
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }
        if ($operator === null && $value !== null) {
            $operator = '=';
        }

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

    public function insert(array $data): BuilderInterface
    {
        $this->operation = 'insert';
        $this->pendingData = $data;
        return $this;
    }

    public function update(array $data): BuilderInterface
    {
        $this->operation = 'update';
        $this->pendingData = $data;
        return $this;
    }

    public function delete(): BuilderInterface
    {
        $this->operation = 'delete';
        return $this;
    }

    public function execute()
    {
        throw new \BadMethodCallException('execute() must be implemented in the concrete builder.');
    }
}