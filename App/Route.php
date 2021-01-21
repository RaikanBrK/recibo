<?php
namespace App;

use MF\Init\Bootstrap;
class Route extends Bootstrap {
	protected function initRoutes() {
		$routes['home'] = array(
			'route' => '/',
			'controller' => 'indexController',
			'action' => 'index'
		);

		$routes['dashboard'] = array(
			'route' => '/dashboard',
			'controller' => 'indexController',
			'action' => 'dashboard'
		);

		$this->setRoutes($routes);
	}
}
?>