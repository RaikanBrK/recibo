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
			"scope" => ["email"],
			'auth_type' => 'rerequest',
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

		$emailExist = function($authSocialId, $reportSession = 'email_cadastro_exist', $redirection = '/cadastro#social') {
			switch ($authSocialId) {
				case 1:
					//Email
					$authSocial = 'sistema de cadastro do site';
					break;
				case 2:
					// Facebook
					$authSocial = 'facebook';
					break;
				case 3:
					// Google
					$authSocial = 'google';
					break;
				default:
					$authSocial = 'novo recurso';
					break;
			}
			$this->report->createReport('warning', $reportSession,
				'Seu email já foi cadastrado anteriormente usando o '.$authSocial.'. Faça seu <a href="/login" title="link para login">login</a>',
			$redirection);
		};

		if ($paramFacebook) {
			/**
			 * Auth Facebook
			 */
			$facebook = new Facebook(FACEBOOK_CADASTRO);

			if ($paramError) {
				// Tratar erro
				$this->report->createReport('error', 'facebook_no_permission', '
					Não foi possível criar sua conta. Permita a <a href="/" title="Home">Receipts</a> suas informações <a href="/termos-de-privacidade" title="termos de privacidade">(veja nossos termos de privacidade)</a> ou escolha outros meios de cadastro
				', '/cadastro#social');
			}

			if ($paramCode) {
				$token = $facebook->getAccessToken("authorization_code", [
					"code" => $paramCode
				]);

				$user = unserialize(serialize($facebook->getResourceOwner($token)));
				$nome = $user->getFirstName() . ' ' . $user->getLastName();

				if ($user->getEmail() == null) {
					// Sem permissão para acessar o email;
					$urlFacebook = $facebook->getAuthorizationUrl([
						"scope" => ["email"],
						'auth_type' => 'rerequest',
					]);
					$this->report->createReport('error', 'facebook_no_permission', '
						Não foi possível criar sua conta. Permita a <a href="/" title="Home">Receipts</a> ter acesso ao seu email para continuar a criar sua conta.
						<a href="'.$urlFacebook.'" title="Autorizar email do facebook">Autorizar email</a>
					', '/cadastro#social');
				} else {
					$usuarios = Container::getModel('Usuarios');
					$usuarios->__set('nome', $nome);
					$usuarios->__set('email', $user->getEmail());
					$usuarios->__set('authSocialId', 2);

					if ($usuarios->emailNoExist()) {
						$usuarios->createUserFromSocial();
						$_SESSION['tokenUserSocialLoginAutomatico'] = [
							"token" => $token,
							"user" => $user->toArray(),
						];
						$this->report->createReport('success', 'facebook_create_account', 'Conta criada com sucesso. Faça login automaticamente, <a href="/logar-usuario-social-automaticamente?facebook=true">login automático</a>', '/cadastro#social');
					} else {
						// Email já existe;
						$emailExist($usuarios->verificarAuthSocialId()['authSocialId'], 'facebook_cadastro_exist');
					}
				}
			}
		} else if ($paramGoogle) {
			/**
			 * Auth Google
			 */
			$google = new Google(GOOGLE_CADASTRO);

			if ($paramError) {
				$this->report->createReport('error', 'google_no_permission', 'Não foi possível acessar sua conta do google', '/cadastro#social');
			}

			if ($paramCode) {
				$token = $google->getAccessToken("authorization_code", [
					"code" => $paramCode
				]);

				$user = unserialize(serialize($google->getResourceOwner($token)));

				if ($user->getFirstName() == null || $user->getLastName() == null || $user->getEmail() == null) {
					$this->report->createReport('error', 'google_no_permission_dados', 'Não foi possível acessar os dados da sua conta do google. Tente criar sua conta novamente se o error persistir entre em contato com o suporte', '/cadastro#social');
				} else {
					$nome = $user->getFirstName() . ' ' . $user->getLastName();
					$usuarios = Container::getModel('Usuarios');
					$usuarios->__set('nome', $nome);
					$usuarios->__set('email', $user->getEmail());
					$usuarios->__set('authSocialId', 3);

					if ($usuarios->emailNoExist()) {
						$usuarios->createUserFromSocial();
						$_SESSION['tokenUserSocialLoginAutomatico'] = [
							"token" => $token,
							"user" => $user->toArray(),
						];
						$this->report->createReport('success', 'google_create_account', 'Conta criada com sucesso. Faça login automaticamente, <a href="/logar-usuario-social-automaticamente?google=true">login automático</a>', '/cadastro#social');
					} else {
						// Email já existe;
						$emailExist($usuarios->verificarAuthSocialId()['authSocialId'], 'google_cadastro_exist');
					}
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
					$this->report->createReport('success', 'email_create_account', 'Conta criada com sucesso. Agora faça seu <a href="/login" title="link para login">login</a>', '/cadastro');
				} else {
					// Email já existe;
					$emailExist($usuarios->verificarAuthSocialId()['authSocialId'], 'email_email_exist', '/cadastro');
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
				$this->report->createReport('error', 'facebook_no_permission', '
					Não foi possível criar sua conta. Permita a <a href="/" title="Home">Receipts</a> suas informações <a href="/termos-de-privacidade" title="termos de privacidade">(veja nossos termos de privacidade)</a> ou escolha outros meios de login
				', '/login#social');
			}

			if ($paramCode) {
			    $token = $facebook->getAccessToken("authorization_code", [
					"code" => $paramCode
				]);

				$user = unserialize(serialize($facebook->getResourceOwner($token)));

				if ($user->getEmail() == null) {
					// Sem permissão para acessar o email;
					$urlFacebook = $facebook->getAuthorizationUrl([
						"scope" => ["email"],
						'auth_type' => 'rerequest',
					]);
					$this->report->createReport('error', 'facebook_no_permission', '
						Não foi possível acessar sua conta. Permita a <a href="/" title="Home">Receipts</a> ter acesso ao seu email para continuar.
						<a href="'.$urlFacebook.'" title="Autorizar email do facebook">Autorizar email</a>
					', '/login#social');
				} else {
					// Com permissão para acessar o email;
					$usuarios = Container::getModel('Usuarios');
					$usuarios->__set('email', $user->getEmail());
					$usuarios->__set('authSocialId', 2);

					if ($usuarios->emailNoExist()) {
						// Email não existe
						$_SESSION['tokenUserSocialCadastroAutomatico'] = [
							"token" => $token,
							"user" => $user->toArray(),
						];
						$this->report->createReport('success', 'facebook_create_account', 'Seu email ainda não foi cadastrado no site. Faça seu cadastro automaticamente, <a href="/cadastrar-usuario-social-automaticamente?facebook=true">cadastro automático</a>', '/login#social');
					} else {
						// Logar usuario;
						$this->logarUsuarioSocial($usuarios->__get('email'), $usuarios->__get('authSocialId'));
						$this->report->createReport('success', 'login_success', 'Logado com sucesso.', '/dashboard#social');
					}
				}
			}
		} else if ($paramGoogle) {
			/**
			 * Auth Google
			 */
			$google = new Google(GOOGLE_LOGIN);

			if ($paramError) {
				$this->report->createReport('error', 'google_login_no_permission', '
					Não foi possível criar sua conta. Permita a <a href="/" title="Home">Receipts</a> suas informações <a href="/termos-de-privacidade" title="termos de privacidade">(veja nossos termos de privacidade)</a> ou escolha outros meios de login
				', '/login#social');
			}

			if ($paramCode) {
				$token = $google->getAccessToken("authorization_code", [
					"code" => $paramCode
				]);

				$user = unserialize(serialize($google->getResourceOwner($token)));

				if ($user->getEmail() == null) {
					// Sem permissão para acessar o email;
					$urlGoogle = $google->getAuthorizationUrl([
						"scope" => ["email"],
						'auth_type' => 'rerequest',
					]);
					$this->report->createReport('error', 'google_no_permission', '
						Não foi possível acessar sua conta. Permita a <a href="/" title="Home">Receipts</a> ter acesso ao seu email para continuar.
						<a href="'.$urlGoogle.'" title="Autorizar email do google">Autorizar email</a>
					', '/login#social');
				} else {
					$usuarios = Container::getModel('Usuarios');
					$usuarios->__set('email', $user->getEmail());
					$usuarios->__set('authSocialId', 3);

					if ($usuarios->emailNoExist()) {
						// Email não existe
						$_SESSION['tokenUserSocialCadastroAutomatico'] = [
							"token" => $token,
							"user" => $user->toArray(),
						];
						$this->report->createReport('success', 'google_create_account', 'Seu email ainda não foi cadastrado no site. Faça seu cadastro automaticamente, <a href="/cadastrar-usuario-social-automaticamente?google=true">cadastro automático</a>', '/login#social');
					} else {
						// Logar usuario;
						$this->logarUsuarioSocial($usuarios->__get('email'), $usuarios->__get('authSocialId'));
						$this->report->createReport('success', 'login_success', 'Logado com sucesso.', '/dashboard#social');
					}
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

	public function logarUsuarioSocialAutomaticamente() {
		if (isset($_SESSION['tokenUserSocialLoginAutomatico'])) {
			$user = $_SESSION['tokenUserSocialLoginAutomatico'];

			if (isset($_GET['facebook'])) {
				$authSocialId = 2;
			} else if (isset($_GET['google'])) {
				$authSocialId = 3;
			} else {
				$this->report->createReport('error', 'login_automatico_fail_auth', 'Login automático inválido.', '/login');
				return false;
			}

			$email = $user['user']['email'] ?? false;

			if ($email && $authSocialId) {
				$retorno = $this->logarUsuarioSocial($email, $authSocialId);

				if ($retorno == false) {
					$this->report->createReport('error', 'login_automatico_fail_conta_no_exist', 'Essa conta não existe.', '/login');
					return false;
				}

				$this->report->createReport('success', 'success_login', 'Login realizado com sucesso.', '/dashboard#social');
			} else {
				$this->report->createReport('error', 'login_automatico_fail_dados', 'Login automático inválido.', '/login');
				return false;
			}
		} else {
			$this->report->createReport('error', 'login_automatico_fail_session', 'Login automático inválido.', '/login');
		}
	}

	public function cadastrarUsuarioSocialAutomaticamente() {
		if (isset($_SESSION['tokenUserSocialCadastroAutomatico'])) {
			$user = $_SESSION['tokenUserSocialCadastroAutomatico'];

			if (isset($_GET['facebook'])) {
				$authSocialId = 2;
				$nome = $user['user']['first_name'] . ' ' . $user['user']['last_name'];
				$email = $user['user']['email'];
			} else if (isset($_GET['google'])) {
				$authSocialId = 3;
				$nome = $user['user']['given_name'] . ' ' . $user['user']['family_name'];
				$email = $user['user']['email'];
			} else {
				$this->report->createReport('error', 'cadastro_automatico_fail_auth', 'Cadastro automático inválido.', '/cadastro');
				return false;
			}

			$email = $user['user']['email'] ?? false;

			if ($email && $authSocialId) {
				$usuarios = Container::getModel('Usuarios');

				$usuarios->__set('nome', $nome);
				$usuarios->__set('email', $email);
				$usuarios->__set('authSocialId', $authSocialId);

				if ($usuarios->emailNoExist()) {
					$usuarios->createUserFromSocial();
					$_SESSION['tokenUserSocialLoginAutomatico'] = [
						"token" => $user['token'],
						"user" => $user['user'],
					];
					$this->report->createReport('success', 'facebook_create_account', 'Conta criada com sucesso. Faça login automaticamente, <a href="/logar-usuario-social-automaticamente?facebook=true">login automático</a>', '/cadastro#social');
				} else {
					// Email já existe;
					$this->report->createReport('error', 'cadastro_automatico_fail_email_exist', 'Cadastro automático inválido', '/login');
					return false;
				}
			} else {
				$this->report->createReport('error', 'cadastro_automatico_fail_email_exist', 'Sem permissão para acesar o email.', '/login');
				return false;
			}
		} else {
			$this->report->createReport('error', 'cadastro_automatico_fail_session', 'Cadastro automático inválido', '/login');
		}
	}

	public function logarUsuarioSocial($email, $authSocialId) {
		$crypt = new Crypt();

		$usuarios = Container::getModel('Usuarios');

		$usuarios->__set('email', $email);
		$usuarios->__set('authSocialId', $authSocialId);

		$user = $usuarios->getUserSocial();

		if ($user) {
			$crypt->__set('text', $user['id']);
			$id = $crypt->encrypt();

			$dados = [
				'id' => $id,
				'authSocialId' => $user['authSocialId'],
			];
			setcookie("remember_user", json_encode($dados), time()+259200);
			$_SESSION['user'] = $dados;
			return true;
		}
		return false;
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