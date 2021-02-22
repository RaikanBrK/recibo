<?php
namespace App\Controllers;
use MF\Controller\Action;
use MF\Model\Container;
use App\Tools\RegrasCadastroUsuario;
use App\Tools\SendMail;
use App\Tools\Crypt;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Facebook;

class AuthController extends Action {
	public function cadastro() {
		$this->view->css = ['auth'];
		$this->view->js = ['auth', 'cadastro'];
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
				$this->report->createReport('error', 'facebook_no_permission', 'Não foi possível acessar sua conta do facebook. Conceda permissão para proseguir.', '/cadastro#social');
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
					$this->report->createReport('success', 'facebook_create_account', 'Conta criada com sucesso.', '/dashboard#social');
				} else {
					// Email já existe;
					$this->report->createReport('warning', 'facebook_email_exist', 'Seu email já está cadastro no nosso site.', '/cadastro#social');
				}
			}
		} else if ($paramGoogle) {
			/**
			 * Auth Google
			 */
			$google = new Google(GOOGLE);

			if ($paramError) {
				$this->report->createReport('error', 'google_no_permission', 'Não foi possível acessar sua conta do google. Conceda permissão para proseguir.', '/cadastro#social');
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
					$this->report->createReport('success', 'google_create_account', 'Conta criada com sucesso.', '/dashboard#social');
				} else {
					// Email já existe;
					$this->report->createReport('warning', 'google_email_exist', 'Seu email já está cadastro no nosso site.', '/cadastro#social');
				}
			}
		} else {
			# Login Email
			$valid = $regras->validarAll($_POST);

			if ($valid) {
				$crypt = new Crypt();
				$crypt->__set('text', $regras->__get('senha'));
				$senhaCriptografada = $crypt->encryptSenhaComplet();

				$usuarios = Container::getModel('Usuarios');
				$usuarios->__set('nome', $regras->__get('nome'));
				$usuarios->__set('email', $regras->__get('email'));
				$usuarios->__set('senha', $senhaCriptografada);

				if ($usuarios->emailNoExist()) {
					$usuarios->createUserFromEmail();
					$sendMail = new SendMail();
					$sendMail->__set('email', $regras->__get('email'));
					$sendMail->__set('nome', $regras->__get('nome'));
					$sendMail->send();
					$this->report->createReport('success', 'email_create_account', 'Conta criada com sucesso. Agora faça seu login.', '/login');
				} else {
					// Email já existe;
					$this->report->createReport('warning', 'email_email_exist', 'Seu email já está cadastro no nosso site.', '/cadastro');
				}
			} else {
				$fail = $regras->__get('fail');
				$dados = [
					'nome' => $regras->__get('nome'),
					'email' => $regras->__get('email'),
				];

				$this->report->createReport('error', 'email_dados_invalid', 'Mensagem de erro', '/cadastro', false, $dados);
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