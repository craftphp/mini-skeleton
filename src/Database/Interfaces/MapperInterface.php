<?php
namespace Craft\Database\Interfaces;

interface MapperInterface {
    public function find($id);
    public function all();
    public function where($column, $operator, $value);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}