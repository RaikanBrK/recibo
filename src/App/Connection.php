<?php
namespace App;

class Connection {
	public static function getDB() {
		try {
			$conn = new \PDO(
				"mysql:host=". DB['host'] .";dbname=". DB['dbname'] .";charset=utf8",
				DB['user'],
				DB['password']
			);
			return $conn;
		} catch (\PDOException $e) {
			// Tratar erro conexao;
		}
	}
}
?>