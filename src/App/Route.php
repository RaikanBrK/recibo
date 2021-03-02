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

		$routes['recibo'] = array(
			'route' => '/recibo/{}',
			'controller' => 'IndexController',
			'action' => 'recibo'
		);

		/**
		 * AuthController
		 */
		$routes['cadastro'] = array(
			'route' => '/cadastro',
			'controller' => 'AuthController',
			'action' => 'cadastro'
		);

		$routes['cadastrarUsuario'] = array(
			'route' => '/cadastrar-usuario',
			'controller' => 'AuthController',
			'action' => 'cadastrarUsuario'
		);

		$routes['cadastrarUsuarioSocialAutomaticamente'] = array(
			'route' => '/cadastrar-usuario-social-automaticamente',
			'controller' => 'AuthController',
			'action' => 'cadastrarUsuarioSocialAutomaticamente'
		);

		$routes['login'] = array(
			'route' => '/login',
			'controller' => 'AuthController',
			'action' => 'login'
		);

		$routes['logarUsuario'] = array(
			'route' => '/logar-usuario',
			'controller' => 'AuthController',
			'action' => 'logarUsuario'
		);

		$routes['logarUsuarioSocialAutomaticamente'] = array(
			'route' => '/logar-usuario-social-automaticamente',
			'controller' => 'AuthController',
			'action' => 'logarUsuarioSocialAutomaticamente'
		);

		$routes['forgotPassword'] = array(
			'route' => '/forgot-password',
			'controller' => 'AuthController',
			'action' => 'forgotPassword'
		);

		$routes['forgotPasswordCode'] = array(
			'route' => '/forgot-password-code',
			'controller' => 'AuthController',
			'action' => 'forgotPasswordCode'
		);

		$routes['email_exist'] = array(
			'route' => '/email_exist',
			'controller' => 'AuthController',
			'action' => 'email_exist'
		);

		$routes['logout'] = array(
			'route' => '/logout',
			'controller' => 'AuthController',
			'action' => 'logout'
		);

		$this->setRoutes($routes);
	}
}
?>