<?php
namespace Craft\Database\Adapter;

use Craft\Database\Interfaces\AdapterInterface;

/**
 * #### PDO SQLite Database Adapter using pdo_sqlite extension
 * **Require**: the `pdo_sqlite` PHP extension.
 */
class PdoSqliteAdapter implements AdapterInterface
{
	protected $pdo;

	/**
	 * Get connection
	 */
	public function connect(array $config)
	{
		if (!extension_loaded('pdo_sqlite')) {
			throw new \Exception('PDO SQLite extension is not loaded.');
		}
		$dsn = 'sqlite:' . ($config['database'] ?? ':memory:');
		$this->pdo = new \PDO($dsn);
		$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function disconnect()
	{
		$this->pdo = null;
	}

	public function query($sql, $params = [])
	{
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($params);
		return $stmt;
	}

	public function fetch($result, $type = 'assoc')
	{
		switch ($type) {
			case 'num':
				return $result->fetch(\PDO::FETCH_NUM);
			case 'both':
				return $result->fetch(\PDO::FETCH_BOTH);
			case 'object':
				return $result->fetch(\PDO::FETCH_OBJ);
			case 'assoc':
			default:
				return $result->fetch(\PDO::FETCH_ASSOC);
		}
	}

	public function fetchAll($result, $type = 'assoc')
	{
		$data = [];
		while ($row = $this->fetch($result, $type)) {
			$data[] = $row;
		}
		return $data;
	}

	public function getError()
	{
		return $this->pdo ? $this->pdo->errorInfo() : null;
	}

	public function lastInsertId()
	{
		return $this->pdo->lastInsertId();
	}

	public function beginTransaction()
	{
		$this->pdo->beginTransaction();
	}

	public function commit()
	{
		$this->pdo->commit();
	}

	public function rollback()
	{
		$this->pdo->rollBack();
	}
}