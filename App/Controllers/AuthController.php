<?php
namespace App\Controllers;
use MF\Controller\Action;
use MF\Model\Container;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;

class AuthController extends Action {
	public function cadastro() {
		$this->view->css = ['auth'];
		$this->view->js = ['auth'];

		$error = filter_input(INPUT_GET, "error", FILTER_SANITIZE_STRING);
		$code = filter_input(INPUT_GET, "code", FILTER_SANITIZE_STRING);

		/**
		 * Auth Google
		 */
		// $google = new Google(GOOGLE);
		// $this->view->authUrl = $google->getAuthorizationUrl();

		// if ($error) {
		// 	// Tratar erro
		// }

		// if ($code) {
		// 	$token = $google->getAccessToken("authorization_code", [
		// 		"code" => $code
		// 	]);

		// 	$user = unserialize(serialize($google->getResourceOwner($token)));
		// 	$infoUser = $user->toArray();

		// 	echo '<pre>';
		// 		print_r($user);
		// 	echo '</pre>';

		// 	echo '<pre>';
		// 		print_r($infoUser);
		// 	echo '</pre>';
		// }

		/**
		 * Auth Facebook
		 */
		// $facebook = new Facebook(FACEBOOK);
		// $this->view->authUrlFacebook = $facebook->getAuthorizationUrl([
		// 	"scope" => ["email"]
		// ]);

		// if ($error) {
		// 	// Tratar erro
		// }

		// if ($code) {
		// 	$token = $facebook->getAccessToken("authorization_code", [
		// 		"code" => $code
		// 	]);

		// 	$user = unserialize(serialize($facebook->getResourceOwner($token)));
		// 	$infoUser = $user->toArray();

		// 	echo '<pre>';
		// 		print_r($user);
		// 	echo '</pre>';

		// 	echo '<pre>';
		// 		print_r($infoUser);
		// 	echo '</pre>';
		// }

		$this->render('cadastro', 'layoutAuth');
	}

	public function login() {
		$this->view->css = ['auth'];
		$this->view->js = ['auth'];

		$this->render('login', 'layoutAuth');
	}
}
?>