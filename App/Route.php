<?php
namespace App;

use MF\Init\Bootstrap;
class Route extends Bootstrap {
	protected function initRoutes() {

		/**
		 * IndexController
		 */
		$routes['home'] = array(
			'route' => '/',
			'controller' => 'IndexController',
			'action' => 'index'
		);

		$routes['dashboard'] = array(
			'route' => '/dashboard',
			'controller' => 'IndexController',
			'action' => 'dashboard'
		);

		$routes['modelos_recibo'] = array(
			'route' => '/modelos_recibo',
			'controller' => 'IndexController',
			'action' => 'dashboard'
		);

		$routes['recibos'] = array(
			'route' => '/recibos',
			'controller' => 'IndexController',
			'action' => 'dashboard'
		);

		/**
		 * AuthController
		 */
		$routes['cadastro'] = array(
			'route' => '/cadastro',
			'controller' => 'AuthController',
			'action' => 'cadastro'
		);

		$routes['login'] = array(
			'route' => '/login',
			'controller' => 'AuthController',
			'action' => 'login'
		);

		$this->setRoutes($routes);
	}
}
?>