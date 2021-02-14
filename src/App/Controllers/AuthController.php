<?php
namespace App\Controllers;
use MF\Controller\Action;
use MF\Model\Container;
use App\Tools\RegrasCadastroUsuario;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;

class AuthController extends Action {
	public function cadastro() {
		$this->view->css = ['auth'];
		$this->view->js = ['auth'];
		$this->view->title = 'Cadastrar usuário';

		$facebook = new Facebook(FACEBOOK);
		$this->view->authUrlFacebook = $facebook->getAuthorizationUrl([
			"scope" => ["email"]
		]);

		$google = new Google(GOOGLE);
		$this->view->authUrlGoogle = $google->getAuthorizationUrl();

		$this->render('cadastro', 'layoutAuth');
	}

	public function cadastrarUsuario() {
		$regras = new RegrasCadastroUsuario();

		$paramFacebook = filter_input(INPUT_GET, 'facebook', FILTER_SANITIZE_STRING);
		$paramGoogle = filter_input(INPUT_GET, 'google', FILTER_SANITIZE_STRING);

		$paramError = filter_input(INPUT_GET, 'error', FILTER_SANITIZE_STRING);
		$paramCode = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);

		if ($paramFacebook) {
			/**
			 * Auth Facebook
			 */
			$facebook = new Facebook(FACEBOOK);

			if ($paramError) {
				// Tratar erro
			}

			if ($paramCode) {
				$token = $facebook->getAccessToken("authorization_code", [
					"code" => $paramCode
				]);

				$user = unserialize(serialize($facebook->getResourceOwner($token)));
				$nome = $user->getFirstName() . ' ' . $user->getLastName();

				$usuarios = Container::getModel('Usuarios');
				$usuarios->__set('nome', $nome);
				$usuarios->__set('email', $user->getEmail());
				$usuarios->__set('authSocialId', 2);

				if ($usuarios->emailNoExist()) {
					$usuarios->createUserFromSocial();
				} else {
					// Email já existe;
				}
			}


		} else if ($paramGoogle) {
			/**
			 * Auth Google
			 */
			$google = new Google(GOOGLE);

			if ($paramError) {
				// Tratar erro
			}

			if ($paramCode) {
				$token = $google->getAccessToken("authorization_code", [
					"code" => $paramCode
				]);

				$user = unserialize(serialize($google->getResourceOwner($token)));

				$nome = $user->getFirstName() . ' ' . $user->getLastName();

				$usuarios = Container::getModel('Usuarios');
				$usuarios->__set('nome', $nome);
				$usuarios->__set('email', $user->getEmail());
				$usuarios->__set('authSocialId', 3);

				if ($usuarios->emailNoExist()) {
					$usuarios->createUserFromSocial();
				} else {
					// Email já existe;
				}
			}
		} else {
			# Login Email
			$valid = $regras->validarAll($_POST);

			if ($valid) {
				$usuarios = Container::getModel('Usuarios');
				$usuarios->__set('nome', $regras->__get('nome'));
				$usuarios->__set('email', $regras->__get('email'));
				$usuarios->__set('senha', $regras->__get('senha'));

				if ($usuarios->emailNoExist()) {
					$usuarios->createUserFromEmail();
				} else {
					// Email já existe;
				}
			} else {
				echo '<pre>';
					print_r($regras->__get('fail'));
				echo '</pre>';
			}
		}
	}

	public function login() {
		$this->view->css = ['auth'];
		$this->view->js = ['auth'];

		$this->render('login', 'layoutAuth');
	}
}
?>