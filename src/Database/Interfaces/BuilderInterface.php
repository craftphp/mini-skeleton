<?php
namespace Craft\Database\Interfaces;

/**
 * Interface for building and executing database queries.
 */
interface BuilderInterface {
    /** Set the table to operate on */
    public function table(string $table): self;
    /** Begin a SELECT query */
    public function select($columns = ['*']): self;
    /** Add a WHERE clause */
    public function where($column, $value = null, $operator = "="): self;
    /** Add an OR WHERE clause */
    public function orWhere($column, $value = null): self;
    /** Begin a pending INSERT; call execute() to run and get last insert id */
    public function insert(array $data): self;
    /** Begin a pending UPDATE; call execute() to run and get affected rows */
    public function update(array $data): self;
    /** Begin a pending DELETE; call execute() to run and get affected rows */
    public function delete(): self;
    /** Execute pending operation: returns string insertId for INSERT, int affected rows for UPDATE/DELETE, array for SELECT */
    public function execute();
    /** Get the SQL string for the built query */
    public function toSql(): string;
    /** Get the bindings for the built query */
    public function getBindings(): array;
    /** Fetch all results as an array */
    public function fetchAll(): array;
    /** Fetch results; type can be 'assoc' or 'obj' */
    public function fetch(string $type = 'assoc');
    /** Fetch the first result */
    public function first(string $type = 'assoc');
    public function get(): array;
}