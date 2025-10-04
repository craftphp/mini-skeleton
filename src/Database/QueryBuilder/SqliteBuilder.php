<?php
namespace Craft\Database\QueryBuilder;

class SqliteBuilder extends BaseBuilder {
    public function toSql(): string {
        $op = $this->operation ?: 'select';
        if ($op === 'select') {
            $sql = "SELECT " . implode(',', $this->columns) . " FROM \"{$this->table}\"";
            if ($this->wheres) {
                $where = array_map(function($w) {
                    return "\"{$w[0]}\" {$w[1]} ?";
                }, $this->wheres);
                $sql .= " WHERE " . implode(' AND ', $where);
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
                $where = array_map(function($w) { return "`{$w[0]}` {$w[1]} ?"; }, $this->wheres);
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            return $sql;
        }
        if ($op === 'delete') {
            $sql = "DELETE FROM `{$this->table}`";
            if ($this->wheres) {
                $where = array_map(function($w) { return "`{$w[0]}` {$w[1]} ?"; }, $this->wheres);
                $sql .= " WHERE " . implode(' AND ', $where);
            }
            return $sql;
        }
        return '';
    }
    
    public function insert(array $data): self { return parent::insert($data); }

    public function update(array $data): self { return parent::update($data); }

    public function delete(): self { return parent::delete(); }

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