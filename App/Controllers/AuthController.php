<?php
namespace App\Controllers;
use MF\Controller\Action;
use MF\Model\Container;
use League\OAuth2\Client\Provider\Google;

class AuthController extends Action {
	public function cadastro() {
		$this->view->css = [];
		$this->view->js = [];

		/**
		 * Auth Google
		 */
		$google = new Google(GOOGLE);
		$this->view->authUrl = $google->getAuthorizationUrl();

		$error = filter_input(INPUT_GET, "error", FILTER_SANITIZE_STRING);
		$code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_STRING);

		if ($error) {
			// Tratar erro
		}

		if ($code) {
			$token = $google->getAccessToken("authorization_code", [
				"code" => $code
			]);

			$user = unserialize(serialize($google->getResourceOwner($token)));
			$infoUser = $user->toArray();

			echo '<pre>';
				print_r($user);
			echo '</pre>';

			echo '<pre>';
				print_r($infoUser);
			echo '</pre>';
		}

		$this->render('cadastro', 'layoutAuth');
	}
}
?>