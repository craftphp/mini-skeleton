<?php
namespace Craft\Database\Mapper;

use Craft\Database\Interfaces\MapperInterface;

abstract class BaseMapper implements MapperInterface {
    protected $adapter;
    protected $table;

    public function __construct($adapter, $table) {
        $this->adapter = $adapter;
        $this->table = $table;
    }
}