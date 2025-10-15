<?php
namespace Craft\Database\Adapter;

use Craft\Database\Interfaces\AdapterInterface;

/**
 * #### MySQLi Database Adapter using MySQLi extension
 * **Require**: the `mysqli` PHP extension.
 */
class MysqliAdapter implements AdapterInterface
{
	protected $conn;

	/**
	 * Get connection
	 */
	public function connect(array $config)
	{
		$this->conn = new \mysqli(
			$config['host'] ?? 'localhost',
			$config['user'] ?? 'root',
			$config['password'] ?? '',
			$config['database'] ?? 'manga_reader',
			$config['port'] ?? 3306
		);
		if ($this->conn->connect_error) {
			throw new \Exception('MySQLi connect error: ' . $this->conn->connect_error);
		}
	}

	public function disconnect()
	{
		if ($this->conn) {
			$this->conn->close();
		}
	}

	public function query($sql, $params = [])
	{
		if (empty($params)) {
			$result = $this->conn->query($sql);
			if ($result === false) {
				throw new \Exception('MySQLi query error: ' . $this->conn->error . ' | SQL: ' . $sql);
			}
			return $result;
		}

		$stmt = $this->conn->prepare($sql);
		if ($stmt === false) {
			throw new \Exception('MySQLi prepare error: ' . $this->conn->error . ' | SQL: ' . $sql);
		}

		$types = '';
		$values = [];
		foreach ($params as $param) {
			if (is_int($param)) {
				$types .= 'i';
			} else if (is_float($param)) {
				$types .= 'd';
			} else if (is_null($param)) {
				$types .= 's';
				$param = null;
			} else {
				$types .= 's';
			}
			$values[] = $param;
		}

		$stmt->bind_param($types, ...$values);
		if (!$stmt->execute()) {
			$error = $stmt->error ?: $this->conn->error;
			throw new \Exception('MySQLi execute error: ' . $error . ' | SQL: ' . $sql);
		}

		$result = $stmt->get_result();
		return $result ?: true;
	}

	public function fetch($result, $type = 'assoc')
	{
		switch ($type) {
			case 'num':
				return $result->fetch_array(MYSQLI_NUM);
			case 'both':
				return $result->fetch_array(MYSQLI_BOTH);
			case 'object':
				return $result->fetch_object();
			case 'assoc':
			default:
				return $result->fetch_assoc();
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

	public function lastInsertId()
	{
		return $this->conn->insert_id;
	}

	public function beginTransaction()
	{
		$this->conn->begin_transaction();
	}

	public function commit()
	{
		$this->conn->commit();
	}

	public function rollback()
	{
		$this->conn->rollback();
	}

	public function getError()
	{
		return $this->conn ? $this->conn->error : null;
	}
}