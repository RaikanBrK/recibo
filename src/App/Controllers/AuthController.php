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
	protected $user;
	protected $token;
	protected $facebookCadastro;
	protected $facebookLogin;
	protected $googleCadastro;
	protected $googleLogin;

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		$this->$attr = $value;
	}

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
			/*$this->report->createReport('warning', $reportSession,
				'Seu email já foi cadastrado anteriormente usando o '.$authSocial.'. Faça seu <a href="/login" title="link para login">login</a>',
			$redirection);*/
		};

		if ($paramFacebook) {
			/**
			 * Auth Facebook
			 */
			$this->__set('facebookCadastro', new Facebook(FACEBOOK_CADASTRO));
			$retorno = $this->authGetDados('facebookCadastro', 'facebook_cadastro');

			if ($retorno['status'] == 'ERROR') {
				$this->report->alertReport($retorno['name']);
			} else if ($retorno['status'] == 'OK') {
				$valid = $this->authSetDadosDb('facebook', $retorno['dados']);

				if ($valid['status'] == 'ERROR') {

					if ($valid['name'] == 'cadastro_email_exist') {
						$this->reportEmailExist($retorno['dados']['email']);
					} else {
						$this->report->alertReport($valid['name']);
					}
				} else if ($valid['status'] == 'OK') {

					if ($valid['name'] == 'create_account') {
						$msg = $this->report->__get('msg')['create_account']['msg'];
						$addMsg = 'Faça login automaticamente, '.$this->report->link('/logar-usuario-social-automaticamente?facebok=true', 'login automático', 'logar automaticamente no facebook');
						$this->report->updateMsg('create_account', $msg.' '.$addMsg);
					}
					$this->report->alertReport($valid['name']);
				}
			}
		} else if ($paramGoogle) {
			/**
			 * Auth Google
			 */
			$this->__set('googleCadastro', new Google(GOOGLE_CADASTRO));
			$retorno = $this->authGetDados('googleCadastro', 'google_cadastro');

			if ($retorno['status'] == 'ERROR') {
				$this->report->alertReport($retorno['name']);
			} else if ($retorno['status'] == 'OK') {
				$valid = $this->authSetDadosDb('google', $retorno['dados']);

				if ($valid['status'] == 'ERROR') {
					if ($valid['name'] == 'cadastro_email_exist') {
						$this->reportEmailExist($retorno['dados']['email']);
					} else {
						$this->report->alertReport($valid['name']);
					}
				} else if($valid['status'] == 'OK') {
					if ($valid['name'] == 'create_account') {
						$msg = $this->report->__get('msg')['create_account']['msg'];
						$addMsg = 'Faça login automaticamente, '.$this->report->link('/logar-usuario-social-automaticamente?google=true', 'login automático', 'logar automaticamente no facebook');
						$this->report->updateMsg('create_account', $msg.' '.$addMsg);
					}
					$this->report->alertReport($valid['name']);
				}
			}
		} else {
			/**
			 * Auth Email
			 */
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
					$sendMail->__set('email', $usuarios->__get('email'));
					$sendMail->__set('nome', $usuarios->__get('nome'));
					$sendMail->send();
					$this->report->alertReport('email_create_account');
				} else {
					$this->reportEmailExist($usuarios->__get('email'));
				}
			} else {
				$fail = $regras->__get('fail');
				$dados = [
					'nome' => $regras->__get('nome'),
					'email' => $regras->__get('email'),
				];

				$this->report->alertReport('email_dados_invalid', $dados);;
			}
		}
	}

	public function retorno(String $status, String $name = '', Array $dados = []) {
		$array = [
			'status' => $status,
		];

		if ($name) {
			$array['name'] = $name;
		}

		if ($dados) {
			$array['dados'] = $dados;
		}

		return $array;
	}

	/**
	 * @param string $attr;
	 * @param string $prefix;
	 */
	public function authGetDados(String $attr, String $prefix) {
		$class = $this->__get($attr);

		$paramError = filter_input(INPUT_GET, 'error', FILTER_SANITIZE_STRING);
		$paramCode = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);

		if ($paramError) {
			// Error
			return $this->retorno('ERROR', $prefix.'_no_permission');
		}

		if ($paramCode) {
			$token = $class->getAccessToken("authorization_code", [
				"code" => $paramCode
			]);

			$user = unserialize(serialize($class->getResourceOwner($token)));

			if ($prefix == 'facebook_cadastro') {
				try {
				    $tokenLong = $class->getLongLivedAccessToken($token);
				} catch (Exception $e) {
				    $tokenLong = $token;
				}
			} else {
				$tokenLong = $token;
			}

			if ($user->getEmail() == null) {
				return $this->retorno('ERROR', $prefix.'_no_permission_email');
			}

			$this->__set('user', $user);
			$this->__set('token', $tokenLong);

			$nome = $user->getFirstName() . ' ' . $user->getLastName();
			$dados = [
				'nome' => $nome,
				'email' => $user->getEmail(),
			];

			return $this->retorno('OK', 'authGetDadosSuccess', $dados);
		}
	}

	/**
	 * @param string $prefix
	 * @param array $dados
	 */
	public function authSetDadosDb(String $prefix, Array $dados) {
		$authSocial = Container::getModel('AuthSocial');
		$authSocial->__set('social', $prefix);
		$authSocialId = $authSocial->getId();

		// Verificando se todos os dados existem
		if (isset($dados['nome']) && isset($dados['email']) && $authSocialId) {
			$usuarios = Container::getModel('Usuarios');
			$usuarios->__set('nome', $dados['nome']);
			$usuarios->__set('email', $dados['email']);
			$usuarios->__set('authSocialId', (INT) $authSocialId['id']);

			if ($usuarios->emailNoExist()) {
				// Email para cadastro não existe

				// Criando usuário no banco
				$usuarios->createUserFromSocial();

				$_SESSION['tokenUserSocialLoginAutomatico'] = [
					"token" => $this->__get('token'),
					"user" => $this->__get('user')->toArray(),
				];
				return $this->retorno('OK', 'create_account');
			} else {
				// Email para cadastro já existe
				return $this->retorno('ERROR', 'cadastro_email_exist');
			}
		} else {
			// Dados inválidos
			return $this->retorno('ERROR', 'cadastro_social_dados_fail');
		}
	}

	/**
	 * @param string $email;
	 */
	public function reportEmailExist(String $email) {

		$usuarios = Container::getModel('Usuarios');
		$usuarios->__set('email', $email);
		$social = $usuarios->getTextSocialEmail();

		if ($social) {
			$text = strtolower($social['social']);
			$text = $text == 'email' ? 'sistema de cadastro do site' : $text;

			$msg = $this->report->__get('msg')['cadastro_email_exist']['msg'];
			$addMsg = 'usando o '.$text;
			$msgReport = $msg.' '.$addMsg;

			$this->report->updateMsg('cadastro_email_exist', $msgReport);
			$this->report->alertReport('cadastro_email_exist');
		}
		$this->report->alertReport('cadastro_email_exist_no_exist');
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
				/*$this->report->createReport('error', 'facebook_login_no_permission', '
					Não foi possível acessa sua conta. Permita a <a href="/" title="Home">Receipts</a> suas informações <a href="/termos-de-privacidade" title="termos de privacidade">(veja nossos termos de privacidade)</a> ou escolha outros meios de login
				', '/login#social');*/
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
					/*$this->report->createReport('error', 'facebook_no_permission', '
						Não foi possível acessar sua conta. Permita a <a href="/" title="Home">Receipts</a> ter acesso ao seu email para continuar.
						<a href="'.$urlFacebook.'" title="Autorizar email do facebook">Autorizar email</a>
					', '/login#social');*/
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
						// $this->report->createReport('success', 'facebook_create_account', 'Seu email ainda não foi cadastrado no site. Faça seu cadastro automaticamente, <a href="/cadastrar-usuario-social-automaticamente?facebook=true">cadastro automático</a>', '/login#social');
					} else {
						// Logar usuario;
						$this->logarUsuarioSocial($usuarios->__get('email'), $usuarios->__get('authSocialId'));
						// $this->report->createReport('success', 'login_success', 'Logado com sucesso.', '/dashboard#social');
					}
				}
			}
		} else if ($paramGoogle) {
			/**
			 * Auth Google
			 */
			$google = new Google(GOOGLE_LOGIN);

			if ($paramError) {
				/*$this->report->createReport('error', 'google_login_no_permission', '
					Não foi possível criar sua conta. Permita a <a href="/" title="Home">Receipts</a> suas informações <a href="/termos-de-privacidade" title="termos de privacidade">(veja nossos termos de privacidade)</a> ou escolha outros meios de login
				', '/login#social');*/
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
					/*$this->report->createReport('error', 'google_no_permission', '
						Não foi possível acessar sua conta. Permita a <a href="/" title="Home">Receipts</a> ter acesso ao seu email para continuar.
						<a href="'.$urlGoogle.'" title="Autorizar email do google">Autorizar email</a>
					', '/login#social');*/
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
						// $this->report->createReport('success', 'google_create_account', 'Seu email ainda não foi cadastrado no site. Faça seu cadastro automaticamente, <a href="/cadastrar-usuario-social-automaticamente?google=true">cadastro automático</a>', '/login#social');
					} else {
						// Logar usuario;
						$this->logarUsuarioSocial($usuarios->__get('email'), $usuarios->__get('authSocialId'));
						// $this->report->createReport('success', 'login_success', 'Logado com sucesso.', '/dashboard#social');
					}
				}
			}
		} else {
			$usuarios = Container::getModel('Usuarios');
			$usuarios->__set('email', trim($_POST['email']));

			if ($usuarios->emailNoExist()) {
				// Email já existe;
				// $this->report->createReport('error', 'email_no_exist_login', 'Seu email não está cadastrado.', '/login');
			} else {
				// Logar usuario;
				$validSenha = $this->logarUsuarioEmail($usuarios->__get('email'), trim($_POST['senha']));

				if ($validSenha) {
					// $this->report->createReport('success', 'login_success', 'Logado com sucesso.', '/dashboard');
				} else {
					// $this->report->createReport('error', 'login_senha_error', 'Senha inválida.', '/login');
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
				// $this->report->createReport('error', 'login_automatico_fail_auth', 'Login automático inválido.', '/login');
				return false;
			}

			$email = $user['user']['email'] ?? false;

			if ($email && $authSocialId) {
				$retorno = $this->logarUsuarioSocial($email, $authSocialId);

				if ($retorno == false) {
					// $this->report->createReport('error', 'login_automatico_fail_conta_no_exist', 'Essa conta não existe.', '/login');
					return false;
				}

				// $this->report->createReport('success', 'success_login', 'Login realizado com sucesso.', '/dashboard#social');
			} else {
				// $this->report->createReport('error', 'login_automatico_fail_dados', 'Login automático inválido.', '/login');
				return false;
			}
		} else {
			// $this->report->createReport('error', 'login_automatico_fail_session', 'Login automático inválido.', '/login');
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
				// $this->report->createReport('error', 'cadastro_automatico_fail_auth', 'Cadastro automático inválido.', '/cadastro');
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
					// $this->report->createReport('success', 'facebook_create_account', 'Conta criada com sucesso. Faça login automaticamente, <a href="/logar-usuario-social-automaticamente?facebook=true">login automático</a>', '/cadastro#social');
				} else {
					// Email já existe;
					// $this->report->createReport('error', 'cadastro_automatico_fail_email_exist', 'Cadastro automático inválido', '/login');
					return false;
				}
			} else {
				// $this->report->createReport('error', 'cadastro_automatico_fail_email_exist', 'Sem permissão para acesar o email.', '/login');
				return false;
			}
		} else {
			// $this->report->createReport('error', 'cadastro_automatico_fail_session', 'Cadastro automático inválido', '/login');
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

		// $this->report->createReport('success', 'logout', 'Deslogado com sucesso!');
	}
}
?>