<?php 
namespace App\Controllers;
use MF\Controller\Action;
use MF\Model\Container;

class IndexController extends Action {
	public function index() {
		$this->view->css = [];
		$this->view->js = [];

		$this->render('index');
	}
	
	public function dashboard() {
		$this->view->css = [];
		$this->view->js = [];

		$this->render('dashboard');
	}
}
?>