<?php
namespace MF\Controller;

class User {
	static public function verificarLogin() {
		$_SESSION ?? session_start();

		if (isset($_SESSION['user'])) {
			return true;
		} else {
			if (isset($_COOKIE['remember_user'])) {
				$remember = json_decode($_COOKIE['remember_user']);

				$dados = [
					'authSocialId' => $remember->authSocialId,
					'id' => $remember->id,
				];

				$_SESSION['user'] = $dados;
				return true;
			}
		}
		return false;
	}
}
