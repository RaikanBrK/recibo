<?php
namespace App;

class Connection {
	public static function getDB() {
		try {
			$conn = new \PDO(
				"mysql:host=localhost;dbname=mvc;charset=utf8",
				"root",
				""
			);
			return $conn;
		} catch (\PDOException $e) {
			// Tratar erro conexao;
		}
	}
}
?>