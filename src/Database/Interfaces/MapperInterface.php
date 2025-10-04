<?php
namespace Craft\Database\Interfaces;

interface MapperInterface {
    public function find($id);
    public function all();
    public function where($column, $value, $operator);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function insertGetId(array $data);
    public function executeUpdate(array $data);
    public function executeDelete(array $data);
}