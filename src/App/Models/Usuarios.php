<?php 
namespace App\Models;
use MF\Model\Model;

class Usuarios extends Model {
	protected $id;
	protected $nome;
	protected $email;
	protected $senha;
	protected $authSocialId;

	public function createUserFromEmail() {
		$query = '
			INSERT INTO usuarios(nome, email, senha) VALUES(
				:nome, :email, :senha
			);
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':nome', $this->__get('nome'));
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->bindValue(':senha', $this->__get('senha'));
		$stmt->execute();
	}

	public function createUserFromSocial() {
		$query = '
			INSERT INTO usuarios(nome, email, authSocialId) VALUES(
				:nome, :email, :authSocialId
			);
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':nome', $this->__get('nome'));
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->bindValue(':authSocialId', $this->__get('authSocialId'), \PDO::PARAM_INT);
		$stmt->execute();
	}

	public function emailNoExist() {
		$query = '
			SELECT email FROM usuarios WHERE email = :email
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->rowCount() == 0;
	}

	public function getUserSocial() {
		$query = '
			SELECT id, authSocialId FROM usuarios WHERE email = :email AND (authSocialId between 2 AND 3);
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function getSenhaUserEmail() {
		$query = '
			SELECT id, authSocialId, senha FROM usuarios WHERE email = :email AND authSocialId = 1
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}
}
?>