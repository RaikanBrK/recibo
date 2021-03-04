<?php
namespace App\Models;
use MF\Model\Model;

class Usuarios extends Model {
	protected $id;
	protected $nome;
	protected $email;
	protected $senha;
	protected $img;
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
			INSERT INTO usuarios(nome, email, authSocialId, img) VALUES(
				:nome, :email, :authSocialId, :img
			);
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':nome', $this->__get('nome'));
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->bindValue(':authSocialId', $this->__get('authSocialId'), \PDO::PARAM_INT);
		$stmt->bindValue(':img', $this->__get('img'));
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
			SELECT id, nome, email, authSocialId FROM usuarios WHERE email = :email AND (authSocialId between 2 AND 3);
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function getSenhaUserEmail() {
		$query = '
			SELECT id, authSocialId, senha FROM usuarios WHERE email = :email
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function getUserEmail() {
		$query = '
			SELECT
				us.id, us.authSocialId, us.nome, us.email, auth.social, us.img, us.senha
			FROM
				usuarios as us
				LEFT JOIN auth_social as auth ON(us.authSocialId = auth.id)
			WHERE
				us.email = :email
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function getIdAndNameWithEmail() {
		$query = '
			SELECT
				id, nome
			FROM
				usuarios
			WHERE
				email = :email
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function verificarAuthSocialId() {
		$query = '
			SELECT authSocialId FROM usuarios WHERE email = :email
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function getTextSocialEmail() {
		$query = '
			SELECT us.authSocialId, auth.social
			FROM usuarios as us LEFT JOIN auth_social as auth ON(us.authSocialId = auth.id)
			WHERE email = :email
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':email', $this->__get('email'));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}

	public function getUserId() {
		$query = '
			SELECT
				us.id, us.authSocialId, us.nome, us.email, auth.social, us.img, us.senha
			FROM
				usuarios as us
				LEFT JOIN auth_social as auth ON(us.authSocialId = auth.id)
			WHERE
				us.id = :id
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':id', $this->__get('id'), \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}
}
?>