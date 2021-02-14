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
}
?>