<?php
namespace App\Tools;

class Reportando {
	public function __construct() {
		$this->criandoMensagens();
	}

	/**
	 * @attr string 'name'
	 * @attr number 'time' (opcional)
	 * @attr string 'status'
	 * @attr string 'redirection'
	 * @attr string 'msg'
	 * @attr number 'modelo'
	 * @attr array 'dados'
	 */
	public function criandoMensagens() {
		// Cadastro
		$this->setMsg([
			'name' => 'create_account',
			'status' => 'OK',
			'time' => 12000,
			'redirection' => '/cadastro#social',
			'msg' => 'Conta criada com sucesso.',
		]);

		$this->setMsg([
			'name' => 'cadastro_social_dados_fail',
			'status' => 'ERROR',
			'redirection' => '/cadastro#social',
			'msg' => 'Não foi possível criar sua conta, tivemos problemas em acessar seus dados. Tente novamente mais tarde.',
		]);

		$this->setMsg([
			'name' => 'cadastro_email_exist',
			'status' => 'WARNING',
			'redirection' => '/cadastro#social',
			'msg' => 'Seu email já foi cadastrado anteriormente',
		]);

		$this->setMsg([
			'name' => 'cadastro_email_exist_no_exist',
			'status' => 'ERROR',
			'redirection' => '/cadastro#social',
			'msg' => 'Algo deu errado ao cadastrar seu email. Tente novamente mais tarde.',
		]);

		// Facebook Cadastro
		$this->setMsg([
			'name' => 'facebook_cadastro_no_permission',
			'status' => 'ERROR',
			'redirection' => '/cadastro#social',
			'msg' => 'Não foi possível criar sua conta. Permita o acesso a sua conta do facebook.',
		]);

		$this->setMsg([
			'name' => 'facebook_cadastro_no_permission_email',
			'status' => 'ERROR',
			'redirection' => '/cadastro#social',
			'msg' => 'Não foi possível criar sua conta. Permita o acesso a seu email do facebook.',
		]);

		// Google Cadastro
		$this->setMsg([
			'name' => 'google_cadastro_no_permission',
			'status' => 'ERROR',
			'redirection' => '/cadastro#social',
			'msg' => 'Não foi possível criar sua conta. Permita o acesso a sua conta do google.',
		]);

		$this->setMsg([
			'name' => 'google_cadastro_no_permission_email',
			'status' => 'ERROR',
			'redirection' => '/cadastro#social',
			'msg' => 'Não foi possível criar sua conta. Permita o acesso a seu email do google.',
		]);

		// Email Cadastro
		$this->setMsg([
			'name' => 'email_dados_invalid',
			'status' => 'ERROR',
			'redirection' => '/cadastro',
			'modelo' => false,
		]);

		$this->setMsg([
			'name' => 'email_create_account',
			'status' => 'OK',
			'redirection' => '/cadastro',
			'msg' => 'Conta criada com sucesso. Agora faça seu '.$this->link('/login', 'login', 'link para login'),
		]);

		// Login
		$this->setMsg([
			'name' => 'login_email_no_exist',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => '',
		]);

		$this->setMsg([
			'name' => 'login_success',
			'status' => 'OK',
			'redirection' => '/dashboard#social',
			'msg' => 'Logado com sucesso',
		]);

		$this->setMsg([
			'name' => 'login_dados_fail',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => 'Tivemos problemas ao recuperar os dados da sua conta. Tente mais tarde',
		]);

		$this->setMsg([
			'name' => 'login_senha_invalid',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => 'Senha inválida',
		]);

		// Facebook Login
		$this->setMsg([
			'name' => 'facebook_login_no_permission',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => 'Não foi possível realizar o login. Permita o acesso a sua conta do facebook.',
		]);

		$this->setMsg([
			'name' => 'facebook_login_no_permission_email',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => 'Não foi possível realizar o login. Permita o acesso a seu email do facebook.',
		]);

		// Google Login
		$this->setMsg([
			'name' => 'google_login_no_permission',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => 'Não foi possível realizar o login. Permita o acesso a sua conta do google.',
		]);

		$this->setMsg([
			'name' => 'google_login_no_permission_email',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => 'Não foi possível realizar o login. Permita o acesso a seu email do google.',
		]);

		$this->setMsg([
			'name' => 'email_login_email_no_exist',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => 'Seu email ainda não foi cadastrado no site.',
		]);

		$this->setMsg([
			'name' => 'login_email_para_conta_social',
			'status' => 'ERROR',
			'redirection' => '/login#social',
			'msg' => 'Esse email pertence a uma conta social.',
		]);


		// 


		// Auth
		$this->setMsg([
			'name' => 'logout',
			'status' => 'OK',
			'redirection' => '/dashboard',
			'msg' => 'Deslogado com sucesso',
		]);
	}

	public function modelo1(Array $array) {
        $dados = [];
        switch ($array['status']) {
			case 'OK':
				$dados['status'] = 'content-icon-report-success';
				$dados['icon'] = '<i class="fas fa-check-circle icon-report"></i>';
				break;
			case 'WARNING':
				$dados['status'] = 'content-icon-report-warning';
				$dados['icon'] = '<i class="fas fa-info-circle icon-report"></i> ';
				break;
			default:
				$dados['status'] = 'content-icon-report-error';
				$dados['icon'] = '<i class="fas fa-exclamation-circle icon-report"></i>';
				break;
        }

        $time = $array['time'] ?? 9800;
        $query = '
			<div class="container" id="containerReport">
				<div class="content">
					<div id="report" data-time="'.$time.'">
						<div class="report">
							<div class="content-icon-report '.$dados["status"].'">
								'.$dados["icon"].'
							</div>
							<div class="msg-report">
								<p>
									'.$array['msg'].'
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
        ';
        $this->__set('query', $query);
	}
}
?>