<?php
namespace App\Model;

use Craft\Database\DatabaseManager;

/**
 * Dynamic model proxy to Mapper/Builder.
 *
 * The actual implementation behind this model depends on env('DB_DESIGN'):
 * - 'builder': forwards to Query Builder with fluent methods
 * - 'mapper': forwards to Mapper with CRUD helpers
 * 
 * @method \Craft\Database\Interfaces\BuilderInterface table(string $table)
 * @method \Craft\Database\Interfaces\BuilderInterface select($columns = ['*'])
 * @method \Craft\Database\Interfaces\BuilderInterface where($column, $operator = null, $value = null)
 * @method string insert(array $data)
 * @method string update(array $data)
 * @method string delete()
 * @method array fetchAll()
 * @method mixed fetch(string $type = 'assoc')
 * @method mixed first(string $type = 'assoc')
 * @method array get()
 * @method mixed insertGetId(array $data)
 * @method bool executeUpdate(array $data)
 * @method bool executeDelete()
 * 
 * @method array|null find($id)
 * @method array all()
 * @method array where($column, $operator, $value)
 * @method mixed create(array $data)
 * @method mixed update($id, array $data)
 * @method mixed delete($id)
 */
class Model extends DatabaseManager
{
    /**
     * Summary of table
     * @var string
     */
    protected $table;

    public function __construct()
    {
        parent::__construct();
        $this->mapper = $this->getMapper($this->table);
    }

    /**
     * Handle dynamic method calls into the model.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array([$this->mapper, $method], $args);
    }

    /**
     * Handle dynamic static method calls into the model.
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = new static();
        return call_user_func_array([$instance->mapper, $method], $args);
    }

    /**
     * Get the database adapter instance.
     * @return \Craft\Database\Adapter\MysqliAdapter|\Craft\Database\Adapter\PdoMysqlAdapter|\Craft\Database\Adapter\PdoSqliteAdapter|\Craft\Database\Adapter\Sqlite3Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}