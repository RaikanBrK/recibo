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
						$addMsg = 'Faça login automaticamente, '.$this->report->link('/logar-usuario-social-automaticamente?google=true', 'login automático', 'logar automaticamente no google');
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

			if (strpos($prefix, 'google') === false) {
				try {
				    $tokenLong = $class->getLongLivedAccessToken($token);
				} catch (Exception $e) {
				    $tokenLong = $token;
				}
				$img = $user->getPictureUrl();
			} else {
				$tokenLong = $token;
				$img = $user->getAvatar();
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
				'img' => $img,
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
			$usuarios->__set('img', $dados['img']);
			$usuarios->__set('authSocialId', (INT) $authSocialId['id']);

			if ($usuarios->emailNoExist()) {
				// Email para cadastro não existe

				// Criando usuário no banco
				$usuarios->createUserFromSocial();

				$_SESSION['tokenUserSocialLoginAutomatico'] = [
					"token" => $this->__get('token'),
					"user" => $this->__get('user'),
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
			"scope" => ["email"],
			'auth_type' => 'rerequest',
		]);

		$google = new Google(GOOGLE_LOGIN);
		$this->view->authUrlGoogle = $google->getAuthorizationUrl();

		$this->render('login', 'layoutAuth');
	}

	public function logarUsuario() {
		$regras = new RegrasCadastroUsuario();

		$paramFacebook = filter_input(INPUT_GET, 'facebook', FILTER_SANITIZE_STRING);
		$paramGoogle = filter_input(INPUT_GET, 'google', FILTER_SANITIZE_STRING);

		if ($paramFacebook) {
			/**
			 * Auth Facebook
			 */
			$this->__set('facebookLogin', new Facebook(FACEBOOK_LOGIN));
			$retorno = $this->authGetDados('facebookLogin', 'facebook_login');

			if ($retorno['status'] == 'ERROR') {
				$this->report->alertReport($retorno['name']);
			} else if($retorno['status'] == 'OK') {
				$valid = $this->authLogin($retorno['dados'], 'facebook');

				$this->report->alertReport($valid['name']);
			}
		} else if ($paramGoogle) {
			/**
			 * Auth Google
			 */
			$this->__set('googleLogin', new Google(GOOGLE_LOGIN));
			$retorno = $this->authGetDados('googleLogin', 'google_login');

			if ($retorno['status'] == 'ERROR') {
				$this->report->alertReport($retorno['name']);
			} else if($retorno['status'] == 'OK') {
				$valid = $this->authLogin($retorno['dados'], 'google');

				$this->report->alertReport($valid['name']);
			}
		} else {
			if(isset($_POST['email']) && isset($_POST['senha'])) {
				$retorno = $this->authLogin([
					'email' => trim($_POST['email']),
					'senha' => trim($_POST['senha']),
					'manterConectado' => isset($_POST['manterConectado']),
				], 'email');

				$this->report->alertReport($retorno['name']);
			}
			$this->report->alertReport('not_dados_login_user_url');
		}
	}

	/**
	 * @param array $dados
	 */
	public function authLogin(Array $dados, String $prefix) {
		if (isset($dados['email'])) {

			$usuarios = Container::getModel('Usuarios');
			$usuarios->__set('email', $dados['email']);

			if ($usuarios->emailNoExist() == false) {

				if ($prefix != 'email') {
					// Login Social
					$retorno = $this->logarUsuarioSocial($dados['email']);

					if ($retorno == false) {
						return $this->retorno('ERROR', 'login_dados_fail');
					}
				} else {
					// Login email
					$retorno = $this->logarUsuarioEmail($dados['email'], $dados['senha'], $dados['manterConectado']);

					if ($retorno['status'] == 'ERROR') {
						return $this->retorno('ERROR', $retorno['name']);
					}
				}
				return $this->retorno('OK', 'login_success');
			}

			// Email não existe
			if ($prefix != 'email') {
				$_SESSION['tokenUserSocialCadastroAutomatico'] = [
					"token" => $this->__get('token'),
					"user" => $this->__get('user'),
				];

				$name = $prefix;
				$diretorio = '/cadastrar-usuario-social-automaticamente?'.$name.'=true';
				$title = 'Cadastrar conta automaticamente';

				$msg = ' Faça o cadastro automaticamente pelo '.$name.' '.$this->report->link($diretorio, $title, $title);
			} else {
				$msg = ' '.$this->report->link('/cadastro', 'Faça o cadastro primeiro', 'Cadastrar conta');
			}

			$this->report->updateMsg('login_email_no_exist', 'O email <i class="text-info">'.$dados['email'].'</i> ainda não foi cadastrado no site.'.$msg);
			return $this->retorno('ERROR', 'login_email_no_exist');

		}
		return $this->retorno('ERROR', 'login_dados_fail');
	}


	public function logarUsuarioSocial($email) {
		$usuarios = Container::getModel('Usuarios');
		$usuarios->__set('email', $email);
		$authId = $usuarios->verificarAuthSocialId();

		$usuarios->__set('authSocialId', $authId);

		$crypt = new Crypt();

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

	public function logarUsuarioEmail($email, $senha, $manterConectado) {
		$crypt = new Crypt();

		$usuarios = Container::getModel('Usuarios');
		$usuarios->__set('email', $email);
		$user = $usuarios->getSenhaUserEmail();

		if ($user) {
			if ($user['authSocialId'] == 1) {
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
					if ($manterConectado) {
						setcookie("remember_user", json_encode($dados), time()+259200);
					}
					$_SESSION['user'] = $dados;
					return $this->retorno('OK');
				}
				return $this->retorno('ERROR', 'login_senha_invalid');
			}
			return $this->retorno('ERROR', 'login_email_para_conta_social');
		}
		return $this->retorno('ERROR', 'email_login_email_no_exist');
	}

	public function forgotPassword() {
		$this->view->css = ['forgotPassword'];
		$this->view->js = ['forgotPassword'];

		$this->render('forgotPassword', 'layoutAuth');
	}

	public function logout() {
		if (isset($_SESSION['user'])) {
			unset($_SESSION['user']);
			unset($_COOKIE['remember_user']);
			setcookie('remember_user', null, -1, '/');

			$this->report->alertReport('logout');
		}
		$this->report->alertReport('logout_error_no_login');
	}

	public function logarUsuarioSocialAutomaticamente() {
		$paramFacebook = filter_input(INPUT_GET, 'facebook', FILTER_SANITIZE_STRING);
		$paramGoogle = filter_input(INPUT_GET, 'google', FILTER_SANITIZE_STRING);

		if (isset($_SESSION['tokenUserSocialLoginAutomatico'])) {
			$user = $_SESSION['tokenUserSocialLoginAutomatico']['user'];
			$token = $_SESSION['tokenUserSocialLoginAutomatico']['token'];
			$nome = $user->getFirstName() . ' ' . $user->getLastName();
			$dados = [
				'nome' => $nome,
				'email' => $user->getEmail(),
			];

			if ($paramFacebook) {
				$title = 'facebook';
			} else {
				$title = 'google';
			}

			unset($_SESSION['tokenUserSocialLoginAutomatico']);
			$valid = $this->authLogin($dados, $title);

			$this->report->alertReport($valid['name']);
		} else {
			header('location: /dashboard');
		}
	}

	public function cadastrarUsuarioSocialAutomaticamente() {
		$paramFacebook = filter_input(INPUT_GET, 'facebook', FILTER_SANITIZE_STRING);
		$paramGoogle = filter_input(INPUT_GET, 'google', FILTER_SANITIZE_STRING);

		if (isset($_SESSION['tokenUserSocialCadastroAutomatico'])) {
			$user = $_SESSION['tokenUserSocialCadastroAutomatico']['user'];
			$token = $_SESSION['tokenUserSocialCadastroAutomatico']['token'];

			if ($paramFacebook) {
				$title = 'facebook';
				try {
					$facebook = new Facebook(FACEBOOK_CADASTRO);
				    $tokenLong = $facebook->getLongLivedAccessToken($token);
				} catch (Exception $e) {
				    $tokenLong = $token;
				}
				$img = $user->getPictureUrl();
			} else {
				$title = 'google';
				$tokenLong = $token;
				$img = $user->getAvatar();
			}

			$nome = $user->getFirstName() . ' ' . $user->getLastName();
			$dados = [
				'nome' => $nome,
				'email' => $user->getEmail(),
				'img' => $img,
			];

			$this->__set('user', $user);
			$this->__set('token', $tokenLong);

			unset($_SESSION['tokenUserSocialCadastroAutomatico']);
			$valid = $this->authSetDadosDb($title, $dados);

			if ($valid['status'] == 'ERROR') {
				if ($valid['name'] == 'cadastro_email_exist') {
					$this->reportEmailExist($user->getEmail());
				} else {
					$this->report->alertReport($valid['name']);
				}
			} else if($valid['status'] == 'OK') {
				if ($valid['name'] == 'create_account') {
					$msg = $this->report->__get('msg')['create_account']['msg'];
					$addMsg = 'Faça login automaticamente, '.$this->report->link('/logar-usuario-social-automaticamente?'.$title.'=true', 'login automático', 'logar automaticamente no '.$title);
					$this->report->updateMsg('create_account', $msg.' '.$addMsg);
				}
				$this->report->alertReport($valid['name']);
			}
		} else {
			header('location: /dashboard');
		}
	}
}
?>