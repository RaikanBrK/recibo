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

		$facebook = new Facebook(FACEBOOK_CADASTRO);
		$this->view->authUrlFacebook = $facebook->getAuthorizationUrl([
			"scope" => ["email"]
		]);

		$google = new Google(GOOGLE_CADASTRO);
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
			$facebook = new Facebook(FACEBOOK_CADASTRO);

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
			$google = new Google(GOOGLE_CADASTRO);

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

		$facebook = new Facebook(FACEBOOK_LOGIN);
		$this->view->authUrlFacebook = $facebook->getAuthorizationUrl([
			"scope" => ["email"]
		]);

		$google = new Google(GOOGLE_LOGIN);
		$this->view->authUrlGoogle = $google->getAuthorizationUrl();

		$this->render('login', 'layoutAuth');
	}

	public function logarUsuario() {
		$regras = new RegrasCadastroUsuario();

		$paramFacebook = filter_input(INPUT_GET, 'facebook', FILTER_SANITIZE_STRING);
		$paramGoogle = filter_input(INPUT_GET, 'google', FILTER_SANITIZE_STRING);

		$paramError = filter_input(INPUT_GET, 'error', FILTER_SANITIZE_STRING);
		$paramCode = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);

		if ($paramFacebook) {
			/**
			 * Auth Facebook
			 */
			$facebook = new Facebook(FACEBOOK_LOGIN);

			if ($paramError) {
				// Tratar erro
				$this->report->createReport('error', 'facebook_login_no_permission', 'Não foi possível acessar sua conta do facebook. Conceda permissão para proseguir.', '/login#social');
			}

			if ($paramCode) {
			    $token = $facebook->getAccessToken("authorization_code", [
					"code" => $paramCode
				]);

				$user = unserialize(serialize($facebook->getResourceOwner($token)));

				$usuarios = Container::getModel('Usuarios');
				$usuarios->__set('email', $user->getEmail());
				$usuarios->__set('authSocialId', 2);

				if ($usuarios->emailNoExist()) {
					// Email não existe
					$this->report->createReport('error', 'facebook_login_email_no_exist', 'Seu email ainda não foi cadastrado no site.', '/login#social');
				} else {
					// Logar usuario;
					$this->logarUsuarioSocial($usuarios->__get('email'), $usuarios->__get('authSocialId'));
					$this->report->createReport('success', 'login_success', 'Logado com sucesso.', '/#social');
				}
			}
		} else if ($paramGoogle) {
			/**
			 * Auth Google
			 */
			$google = new Google(GOOGLE_LOGIN);

			if ($paramError) {
				$this->report->createReport('error', 'google_login_no_permission', 'Não foi possível acessar sua conta do google. Conceda permissão para proseguir.', '/cadastro#social');
			}

			if ($paramCode) {
				$token = $google->getAccessToken("authorization_code", [
					"code" => $paramCode
				]);

				$user = unserialize(serialize($google->getResourceOwner($token)));

				$usuarios = Container::getModel('Usuarios');
				$usuarios->__set('email', $user->getEmail());
				$usuarios->__set('authSocialId', 3);

				if ($usuarios->emailNoExist()) {
					// Email não existe
					$this->report->createReport('error', 'google_email_no_exist', 'Seu email ainda não foi cadastrado no site.', '/login#social');
				} else {
					// Logar usuario;
					$this->logarUsuarioSocial($usuarios->__get('email'), $usuarios->__get('authSocialId'));
					$this->report->createReport('success', 'login_success', 'Logado com sucesso.', '/#social');
				}
			}
		} else {
			$usuarios = Container::getModel('Usuarios');
			$usuarios->__set('email', trim($_POST['email']));

			if ($usuarios->emailNoExist()) {
				// Email já existe;
				$this->report->createReport('error', 'email_no_exist_login', 'Seu email não está cadastrado.', '/login');
			} else {
				// Logar usuario;
				$validSenha = $this->logarUsuarioEmail($usuarios->__get('email'), trim($_POST['senha']));

				if ($validSenha) {
					$this->report->createReport('success', 'login_success', 'Logado com sucesso.', '/dashboard');
				} else {
					$this->report->createReport('error', 'login_senha_error', 'Senha inválida.', '/login');
				}
			}
		}
	}

	public function logarUsuarioSocial($email, $authSocialId) {
		$crypt = new Crypt();

		$usuarios = Container::getModel('Usuarios');

		$usuarios->__set('email', $email);
		$usuarios->__set('authSocialId', $authSocialId);

		$user = $usuarios->getUserSocial();

		$crypt->__set('text', $user['id']);
		$id = $crypt->encrypt();

		$dados = [
			'id' => $id,
			'authSocialId' => $user['authSocialId'],
		];
		setcookie("remember_user", json_encode($dados), time()+259200);
		$_SESSION['user'] = $dados;
	}

	public function logarUsuarioEmail($email, $senha) {
		$crypt = new Crypt();

		$usuarios = Container::getModel('Usuarios');
		$usuarios->__set('email', $email);

		$user = $usuarios->getSenhaUserEmail();
		$userSenhaCriptografada = $user['senha'];

		$crypt->__set('text', $userSenhaCriptografada);
		$userSenha = $crypt->decrypt();

		$validSenha = password_verify($senha, $userSenha);

		if($validSenha) {
			$crypt->__set('text', $user['id']);
			$id = $crypt->encrypt();

			$dados = [
				'id' => $id,
				'authSocialId' => $user['authSocialId'],
			];
			setcookie("remember_user", json_encode($dados), time()+259200);
			$_SESSION['user'] = $dados;
		}

		return $validSenha;
	}

	public function logout() {
		unset($_SESSION['user']);
		unset($_COOKIE['remember_user']);
		setcookie('remember_user', null, -1, '/');

		$this->report->createReport('success', 'logout', 'Deslogado com sucesso!');
	}
}
?>