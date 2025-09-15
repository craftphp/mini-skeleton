<?php
namespace Craft\Database;

use Craft\Database\Adapter\MysqliAdapter;
use Craft\Database\Adapter\PdoMysqlAdapter;
use Craft\Database\Adapter\Sqlite3Adapter;
use Craft\Database\Adapter\PdoSqliteAdapter;
use Craft\Database\Mapper\MysqlMapper;
use Craft\Database\Mapper\SqliteMapper;
use Craft\Database\QueryBuilder\MysqlBuilder;
use Craft\Database\QueryBuilder\SqliteBuilder;

/**
 * #### DatabaseManager class
 * 
 * Manages database connections and mappers/builders based on configuration.
 */
class DatabaseManager
{
    /** Adapter instance */
    protected $adapter;
    protected $mapper;
    protected $builder;
    protected $mapperClass;
    protected $builderClass;

    public function __construct()
    {
        $driver = env('DB_DRIVER', 'pdo_mysql');
        $design = env('DB_DESIGN', 'mapper');
        $config = $this->getConfig($driver);

        switch ($driver) {
            case 'mysqli':
                $this->adapter = new MysqliAdapter();
                break;
            case 'sqlite3':
                $this->adapter = new Sqlite3Adapter();
                break;
            case 'pdo_sqlite':
                $this->adapter = new PdoSqliteAdapter();
                break;
            case 'pdo_mysql':
            default:
                $this->adapter = new PdoMysqlAdapter();
        }
        $this->adapter->connect($config);

        if ($design === 'mapper') {
            if (in_array($driver, ['mysqli', 'pdo_mysql'])) {
                $this->mapperClass = MysqlMapper::class;
            } else if (in_array($driver, ['sqlite3', 'pdo_sqlite'])) {
                $this->mapperClass = SqliteMapper::class;
            }
        } else if ($design === 'builder') {
            if (in_array($driver, ['mysqli', 'pdo_mysql'])) {
                $this->mapperClass = MysqlBuilder::class;
            } else if (in_array($driver, ['sqlite3', 'pdo_sqlite'])) {
                $this->mapperClass = SqliteBuilder::class;
            }
        }
    }

    /**
     * Get database configuration based on driver
     * @param mixed $driver
     * 
     */
    protected function getConfig($driver)
    {
        if (strpos($driver, 'sqlite') !== false) {
            return [
                'database' => env('DB_SQLITE_FILE') . '.db',
            ];
        }
        else if (strpos($driver, 'mysql') !== false) {
            return [
                'host'     => env('DB_HOST'),
                'port'     => env('DB_PORT'),
                'user'     => env('DB_USER'),
                'password' => env('DB_PASS') ?? null,
                'database' => env('DB_NAME'),
            ];
        }
    }

    /**
     * Get the current database adapter
     * @return MysqliAdapter|PdoMysqlAdapter|PdoSqliteAdapter|Sqlite3Adapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Get a mapper instance for a specific table
     * 
     *  **Note:**
     * - For 'mapper': the Mapper class receives ($adapter, $table)
     * - For 'builder': the Builder also receives ($adapter, $table) and executes through the adapter
     * @param mixed $table
     * @return object
     */
    public function getMapper($table)
    {
        return new $this->mapperClass($this->adapter, $table);
    }
}