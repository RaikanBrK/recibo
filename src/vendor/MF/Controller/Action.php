<?php

namespace MF\Controller;
use MF\Controller\Report;

abstract class Action{
	protected $view;
	protected $report;

	public function __construct() {
		$this->view = new \stdClass();
		$this->report = new Report();
	}

	public function __set($attr, $value) {
		$this->$attr = $value;
	}

	public function __get($attr) {
		return $this->$attr;
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