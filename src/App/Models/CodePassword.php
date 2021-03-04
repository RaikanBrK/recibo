<?php
namespace App\Models;
use MF\Model\Model;

class CodePassword extends Model {
	protected $id;
	protected $ip;
	protected $codigo;
	protected $status_id;
	protected $usuario_id;
	protected $data_create;

	public function verificarQtdCode() {
		$query = '
			SELECT COUNT(id) as qtd
			FROM codigos_password
			WHERE usuario_id = :usuario_id AND ip = :ip AND status_id = 1 AND data_create > :data_create
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':usuario_id', $this->__get('usuario_id'));
		$stmt->bindValue(':ip', $this->__get('ip'));
		$stmt->bindValue(':data_create', $this->__get('data_create'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function generatorCode() {
		return rand(100000, 999999);
	}

	public function gerarCode() {
		$code = $this->generatorCode();

		$query = '
			SELECT id
			FROM codigos_password
			WHERE codigo = :codigo AND ip = :ip AND status_id = 1
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':codigo', $code);
		$stmt->bindValue(':ip', $this->__get('ip'));
		$stmt->execute();

		if ($stmt->rowCount() != 0) {
			$this->gerarCode();
		}
		return $code;
	}

	public function criarCodigoPassword() {
		$query = '
			INSERT INTO codigos_password(
				ip, codigo, usuario_id
			) VALUES(
				:ip, :codigo, :usuario_id
			);
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':ip', $this->__get('ip'));
		$stmt->bindValue(':codigo', $this->__get('codigo'));
		$stmt->bindValue(':usuario_id', $this->__get('usuario_id'));
		$stmt->execute();
	}

	public function ultimoCodeUser() {
		$query = '
			SELECT data_create
			FROM codigos_password
			WHERE ip = :ip AND usuario_id = :usuario_id
			ORDER BY data_create DESC
			LIMIT 0,1
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':ip', $this->__get('ip'));
		$stmt->bindValue(':usuario_id', $this->__get('usuario_id'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function verificarCode() {
		$query = '
			SELECT
				id, ABS(TIMESTAMPDIFF(MINUTE, CURRENT_TIMESTAMP(), data_create)) as data_diff_minutos
			FROM
				codigos_password
			WHERE
				usuario_id = :usuario_id
				AND codigo = :code
				AND ip = :ip
				AND status_id = 1
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':ip', $this->__get('ip'));
		$stmt->bindValue(':code', $this->__get('codigo'));
		$stmt->bindValue(':usuario_id', $this->__get('usuario_id'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function excluirCode() {
		$query = '
			DELETE FROM codigos_password WHERE id = :id
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id', $this->__get('id'));
		$stmt->execute();
	}
}
?>