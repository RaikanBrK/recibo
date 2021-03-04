<?php
namespace MF\Controller;
use App\Tools\Reportando;

class Report extends Reportando {
	protected $msg;
	protected $query;

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		$this->$attr = $value;
	}

	public function setMsg(Array $msg) {
		$msgAmazenada = $this->__get('msg');
		$msgAmazenada[$msg['name']] = $msg;
		$this->__set('msg', $msgAmazenada);
	}

	public function updateMsg($name, $msg) {
		$msgAmazenada = $this->__get('msg');
		$msgAmazenada[$name]['msg'] = $msg;
		$this->__set('msg', $msgAmazenada);
	}

	public function alertReport($name, $dados = []) {
		if (array_key_exists($name, $this->__get('msg'))) {
			$msg = $this->__get('msg')[$name];
			$msg['valid'] = $msg['status'] != 'ERROR';

			if (isset($msg['ajax'])) {
				echo json_encode($msg);
				die();
			} else {
				$_SESSION['report'][$name] = $msg;
				$_SESSION['report'][$name]['dados'] = $dados;

				if ($msg['redirection'] != false) {
					header('location: '.$msg['redirection']);
					exit();
				}
			}
		}
	}

	public function getReport($name) {
		$retorno = false;

		$retorno = $_SESSION['report'][$name] ?? false;

		if ($retorno) {
			unset($_SESSION['report'][$name]);
		}

		return $retorno;
	}

	public function link($redirection = '/', $text = 'Home', $title = 'Receipts') {
		return '<a href="'.$redirection.'" title="'.$title.'">'.$text.'</a>';
	}

	public function renderModel() {
		echo $this->__get('query');
	}
}