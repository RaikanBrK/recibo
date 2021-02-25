<?php
namespace App\Models;
use MF\Model\Model;

class AuthSocial extends Model {
	protected $id;
	protected $social;

	public function getId() {
		$query = '
			SELECT id FROM auth_social WHERE social = :social
		';

		$stmt = $this->db->prepare($query);
		$stmt->bindValue(':social', ucfirst($this->__get('social')));
		$stmt->execute();
		return $stmt->fetch(\PDO::FETCH_ASSOC);
	}
}
?>