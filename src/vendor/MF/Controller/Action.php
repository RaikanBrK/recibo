<?php

namespace MF\Controller;
use MF\Controller\Report;
use MF\Controller\User;
use MF\Model\Container;
use App\Tools\Crypt;

abstract class Action {
	protected $view;
	protected $report;

	public function __construct() {
		$_SESSION ?? session_start();
		date_default_timezone_set('America/Sao_Paulo');

		$this->view = new \stdClass();
		$this->report = new Report();
		$this->userLogado = User::verificarLogin();
		$this->getDadosUser();
	}

	public function __set($attr, $value) {
		$this->$attr = $value;
	}

	public function __get($attr) {
		return $this->$attr;
	}

	protected function getDadosUser() {
		if ($this->userLogado) {
			$usuarios = Container::getModel('Usuarios');

			if (isset($_SESSION['user']['id'])) {
				$crypt = new Crypt();
				$crypt->__set('text', $_SESSION['user']['id']);
				$id = $crypt->decrypt();

				$usuarios->__set('id', (INT) $id);
				$dados = $usuarios->getUserId();
				$this->view->user = $dados;
			}
		}
	}

	protected function render($view, $layout = 'layout') {
		$this->view->page = $view;
		if (file_exists("../App/Views/".$layout.".phtml")) {
			require_once("../App/Views/".$layout.".phtml");
		} else {
			$this->content();
		}
	}

	protected function content() {
		$classAtual = get_class($this);
		$classAtual = str_replace('App\\Controllers\\', '', $classAtual);
		$classAtual = strtolower(str_replace('Controller', '', $classAtual));

		require_once("../App/Views/".$classAtual."/".$this->view->page.'.phtml');
	}
}


?>