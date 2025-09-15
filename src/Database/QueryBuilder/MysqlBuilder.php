<?php
namespace Craft\Database\QueryBuilder;

class MysqlBuilder extends BaseBuilder {
    public function toSql(): string {
        $sql = "SELECT " . implode(',', $this->columns) . " FROM `{$this->table}`";
        if ($this->wheres) {
            $where = array_map(function($w) { return "`{$w[0]}` {$w[1]} ?"; }, $this->wheres);
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        return $sql;
    }

    public function insert(array $data): string {
        $fields = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        return "INSERT INTO `{$this->table}` (`" . implode('`,`', $fields) . "`) VALUES ($placeholders)";
    }

    public function update(array $data): string {
        $fields = array_keys($data);
        $set = implode(', ', array_map(function($f) { return "`$f` = ?"; }, $fields));
        $sql = "UPDATE `{$this->table}` SET $set";
        if ($this->wheres) {
            $where = array_map(function($w) { return "`{$w[0]}` {$w[1]} ?"; }, $this->wheres);
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        return $sql;
    }

    public function delete(): string {
        $sql = "DELETE FROM `{$this->table}`";
        if ($this->wheres) {
            $where = array_map(function($w) { return "`{$w[0]}` {$w[1]} ?"; }, $this->wheres);
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        return $sql;
    }
}