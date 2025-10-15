<?php
namespace Craft\Database\QueryBuilder;

use Craft\Database\Interfaces\BuilderInterface;

abstract class BaseBuilder implements BuilderInterface {
    protected $adapter;
    protected $table;
    protected $columns = ['*'];
    protected $wheres = []; // mỗi entry: [column, operator, value, boolean('AND'|'OR')]
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
        // Allow passing an associative array: where(['email' => $email, 'role' => 'admin'])
        // Also allow parallel arrays: where(['email','role'], [$email, 'admin'])
        if (is_array($column)) {
            // If second param is provided and is array, treat $column as list of cols
            if (is_array($value)) {
                $cols = array_values($column);
                $vals = array_values($value);
                $count = min(count($cols), count($vals));
                for ($i = 0; $i < $count; $i++) {
                    $this->wheres[] = [$cols[$i], '=', $vals[$i], 'AND'];
                    $this->bindings[] = $vals[$i];
                }
                return $this;
            }

            // associative array: key => value
            foreach ($column as $col => $val) {
                $this->wheres[] = [$col, '=', $val, 'AND'];
                $this->bindings[] = $val;
            }
            return $this;
        }

        // Support shorthand: where('id', $id) => where('id', '=', $id)
        if ($value === null && $operator !== null) {
            $value = $operator;
            $operator = '=';
        }
        if ($operator === null && $value !== null) {
            $operator = '=';
        }

        $this->wheres[] = [$column, $operator, $value, 'AND'];
        $this->bindings[] = $value;
        return $this;
    }

    // New orWhere implementation
    public function orWhere($column, $value = null): BuilderInterface {
        // Reuse where logic but mark boolean as OR
        if (is_array($column)) {
            if (is_array($value)) {
                $cols = array_values($column);
                $vals = array_values($value);
                $count = min(count($cols), count($vals));
                for ($i = 0; $i < $count; $i++) {
                    $this->wheres[] = [$cols[$i], '=', $vals[$i], 'OR'];
                    $this->bindings[] = $vals[$i];
                }
                return $this;
            }

            foreach ($column as $col => $val) {
                $this->wheres[] = [$col, '=', $val, 'OR'];
                $this->bindings[] = $val;
            }
            return $this;
        }

        // shorthand orWhere('col', $val)
        $this->wheres[] = [$column, '=', $value, 'OR'];
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

    /**
     * Execute the built query
     * @throws \BadMethodCallException execution must be implemented in the concrete builder.
     * @return never
     */
    public function execute()
    {
        throw new \BadMethodCallException('execute() must be implemented in the concrete builder.');
    }
}