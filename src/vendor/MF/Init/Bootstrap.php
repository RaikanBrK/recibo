<?php 

namespace MF\Init;

abstract class Bootstrap {
	private $routes;

	abstract protected function initRoutes();

	public function __construct() {
		$this->initRoutes();
		$this->run($this->getUrl());
	}

	public function getRoutes() {
		return $this->routes;
	}

	public function setRoutes(array $routes) {
		$routesEdit = [];
		foreach ($routes as $key => $route) {
			if (isset($route['group'])) {
				$route['route'] = $route['group'] . $route['route'];
			}

			$routesEdit[$key] = $route;
		}

		$this->routes = $routesEdit;
	}

	protected function run($url) {
		foreach ($this->getRoutes() as $key => $route) {
			$validRoute = false;

			if ($url == $route['route']) {
				$validRoute = true;
			} else {

				/**
				 * Rotas Dinâmicas
				 */
				$arrayUrl = explode('/', $url);
				unset($arrayUrl[count($arrayUrl) - 1]);
				$stringUrl = implode('/', $arrayUrl);

				$newUrl = $stringUrl . '/{}';

				if ($newUrl == $route['route']) {
					$validRoute = true;
				}
			}

			if ($validRoute) {
				if (isset($route['group'])) {
					$this->startControllerAction($route['controller'], str_replace('/', '', $route['group']));
				}
				$this->startControllerAction($route['controller'], $route['action']);
			}
		}
	}

	private function startControllerAction($controller, $action) {
		$class = "App\\Controllers\\".ucfirst($controller);
		$controller = new $class;

		if (method_exists($controller, $action)) {
			$controller->$action();
		}
	}

	protected function getUrl() {
		return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	}
}


?>