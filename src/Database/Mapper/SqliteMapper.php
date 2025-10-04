<?php
namespace Craft\Database\Mapper;

class SqliteMapper extends BaseMapper {
    public function find($id) {
        $sql = "SELECT * FROM \"{$this->table}\" WHERE id = ?";
        $result = $this->adapter->query($sql, [$id]);
        return $this->adapter->fetch($result);
    }

    public function all() {
        $sql = "SELECT * FROM \"{$this->table}\"";
        $result = $this->adapter->query($sql);
        return $this->adapter->fetchAll($result);
    }

    public function where($column, $value, $operator = "=") {
        $sql = "SELECT * FROM \"{$this->table}\" WHERE \"{$column}\" {$operator} ?";
        $result = $this->adapter->query($sql, [$value]);
        return $this->adapter->fetchAll($result);
    }

    public function create(array $data) {
        $fields = array_keys($data);
        $placeholders = implode(',', array_fill(0, count($fields), '?'));
        $sql = "INSERT INTO \"{$this->table}\" (\"" . implode('","', $fields) . "\") VALUES ($placeholders)";
        $this->adapter->query($sql, array_values($data));
        return $this->adapter->lastInsertId();
    }

    public function update($id, array $data) {
        $fields = array_keys($data);
        $set = implode(', ', array_map(function($f) { return "\"$f\" = ?"; }, $fields));
        $sql = "UPDATE \"{$this->table}\" SET $set WHERE id = ?";
        $params = array_values($data);
        $params[] = $id;
        return $this->adapter->query($sql, $params);
    }

    public function delete($id) {
        $sql = "DELETE FROM \"{$this->table}\" WHERE id = ?";
        return $this->adapter->query($sql, [$id]);
    }
    public function insertGetId(array $data) {
        $this->create($data);
        return $this->adapter->lastInsertId();
    }
    public function executeUpdate(array $data) {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("ID is required for update.");
        }
        $id = $data['id'];
        unset($data['id']);
        return $this->update($id, $data);
    }
    public function executeDelete(array $data) {
        if (!isset($data['id'])) {
            throw new \InvalidArgumentException("ID is required for delete.");
        }
        $id = $data['id'];
        return $this->delete($id);
    }
}