<?php
namespace Craft\Database\QueryBuilder;

use Craft\Database\Interfaces\BuilderInterface;

class SqliteBuilder extends BaseBuilder implements BuilderInterface {
    public function toSql(): string {
        $op = $this->operation ?: 'select';
        if ($op === 'select') {
            $sql = "SELECT " . implode(',', $this->columns) . " FROM \"{$this->table}\"";
            if ($this->wheres) {
                $whereSql = '';
                foreach ($this->wheres as $i => $w) {
                    $col = $w[0]; $operator = $w[1]; $bool = $w[3] ?? 'AND';
                    $prefix = $i === 0 ? '' : " {$bool} ";
                    $whereSql .= $prefix . "\"{$col}\" {$operator} ?";
                }
                $sql .= " WHERE " . $whereSql;
            }
            return $sql;
        }
        if ($op === 'insert') {
            $fields = array_keys($this->pendingData);
            $placeholders = implode(',', array_fill(0, count($fields), '?'));
            return "INSERT INTO `{$this->table}` (`" . implode('`,`', $fields) . "`) VALUES ($placeholders)";
        }
        if ($op === 'update') {
            $fields = array_keys($this->pendingData);
            $set = implode(', ', array_map(function($f) { return "`$f` = ?"; }, $fields));
            $sql = "UPDATE `{$this->table}` SET $set";
            if ($this->wheres) {
                $whereSql = '';
                foreach ($this->wheres as $i => $w) {
                    $col = $w[0]; $operator = $w[1]; $bool = $w[3] ?? 'AND';
                    $prefix = $i === 0 ? '' : " {$bool} ";
                    $whereSql .= $prefix . "`{$col}` {$operator} ?";
                }
                $sql .= " WHERE " . $whereSql;
            }
            return $sql;
        }
        if ($op === 'delete') {
            $sql = "DELETE FROM `{$this->table}`";
            if ($this->wheres) {
                $whereSql = '';
                foreach ($this->wheres as $i => $w) {
                    $col = $w[0]; $operator = $w[1]; $bool = $w[3] ?? 'AND';
                    $prefix = $i === 0 ? '' : " {$bool} ";
                    $whereSql .= $prefix . "`{$col}` {$operator} ?";
                }
                $sql .= " WHERE " . $whereSql;
            }
            return $sql;
        }
        return '';
    }
    
    public function insert(array $data): BuilderInterface { return parent::insert($data); }

    public function update(array $data): BuilderInterface { return parent::update($data); }

    public function delete(): BuilderInterface { return parent::delete(); }

    /**
     * Execute the built query for SQLite
     * @return array|int|string|null
     */
    public function execute()
    {
        $op = $this->operation ?: 'select';
        if ($op === 'select') {
            return $this->fetchAll();
        }
        if ($op === 'insert') {
            $sql = $this->toSql();
            $bindings = array_values($this->pendingData);
            $this->adapter->query($sql, $bindings);
            return (string) $this->adapter->lastInsertId();
        }
        if ($op === 'update') {
            $sql = $this->toSql();
            $bindings = array_merge(array_values($this->pendingData), $this->getBindings());
            $result = $this->adapter->query($sql, $bindings);
            if (is_object($result) && method_exists($result, 'rowCount')) {
                return (int) $result->rowCount();
            }
            return 0;
        }
        if ($op === 'delete') {
            $sql = $this->toSql();
            $bindings = $this->getBindings();
            $result = $this->adapter->query($sql, $bindings);
            if (is_object($result) && method_exists($result, 'rowCount')) {
                return (int) $result->rowCount();
            }
            return 0;
        }
        return null;
    }
}