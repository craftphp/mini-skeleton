<?php
namespace Craft\Database\Interfaces;

interface BuilderInterface {
    public function table(string $table): self;
    public function select($columns = ['*']): self;
    public function where($column, $operator = null, $value = null): self;
    public function insert(array $data): string;
    public function update(array $data): string;
    public function delete(): string;
    public function toSql(): string;
    public function getBindings(): array;
    public function fetchAll(): array;
    public function fetch(string $type = 'assoc');
    public function first(string $type = 'assoc');
    public function get(): array;
    public function insertGetId(array $data);
    public function executeUpdate(array $data);
    public function executeDelete();
}