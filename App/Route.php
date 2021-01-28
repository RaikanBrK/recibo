<?php
namespace App;

use MF\Init\Bootstrap;
class Route extends Bootstrap {
	protected function initRoutes() {
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

		$this->setRoutes($routes);
	}
}
?>