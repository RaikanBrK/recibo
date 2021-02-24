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
		$this->setMsg([
			'name' => 'facebook_cadastro_no_permission',
			'time' => 9800,
			'status' => 'OK',
			'redirection' => '/dashboard',
			'msg' => 'Erro facebook',
			'modelo' => 1,
		]);
	}

	public function modelo1(Array $array) {
        $dados = [];
        switch ($array['status']) {
			case 'success':
				$dados['status'] = 'content-icon-report-success';
				$dados['icon'] = '<i class="fas fa-check-circle icon-report"></i>';
				break;
			case 'warning':
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