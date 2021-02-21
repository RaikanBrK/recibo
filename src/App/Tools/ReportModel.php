<?php
namespace App\Tools;

class ReportModel {
	protected $query;

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		return $this->$attr = $value;
	}

	public function modelo1(Array $array, $time = 9800) {
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
									'.$array['mensagem'].'
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		';
		$this->__set('query', $query);
	}

	public function renderModel() {
		echo $this->__get('query');
	}
}
?>