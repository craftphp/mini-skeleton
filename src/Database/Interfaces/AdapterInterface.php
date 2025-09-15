<?php
namespace Craft\Database\Interfaces;

interface AdapterInterface{
    public function connect(array $config);
    public function disconnect();
    public function query($sql, $params = []);
    public function fetch($result, $type = 'assoc');
    public function fetchAll($result, $type = 'assoc');
    public function lastInsertId();
    public function beginTransaction();
    public function commit();
    public function rollback();
    public function getError();
}