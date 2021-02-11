<?php 
namespace App\Controllers;
use MF\Controller\Action;
use MF\Model\Container;
use App\Tools\Card;

class IndexController extends Action {
	public function index() {
		$this->view->css = [];
		$this->view->js = [];

		$this->render('index');
	}

	public function dashboard() {
		$this->view->css = ['card', 'dashboard'];
		$this->view->js = ['card', 'dashboard'];

		$this->view->card = new Card();
		$this->view->card->__set('cards',
			$this->receipts()
		);

		$this->render('dashboard');
	}

	public function recibo() {
		$this->view->css = ['card', 'recibo'];
		$this->view->js = ['card'];

		$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

		$ulrArray = explode('/', $url);
		if (isset($ulrArray[2]) && empty($ulrArray[2]) == false) {
			$nameRecibo = $ulrArray[2];

			$this->render('recibo');
		} else {
			header('location: /dashboard');
			exit();
		}
	}










	public function receipts() {
		return [
			[
				"title" => "Recibo simples de serviços",
				"favorit" => 3,
				"evaluation" => 5,
				"img" => "recibo-teste.png",
				"other" => "R$ 15,00",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 5,
				"evaluation" => 4,
				"img" => "figma_recibo-1.png",
				"other" => "LocalWeb",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 201,
				"evaluation" => 5,
				"img" => "recibo-3.jpg",
				"other" => "<i class='fas fa-clipboard-check success-card'></i>",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 1,
				"evaluation" => 3,
				"img" => "recibo-4.jpg",
				"other" => "Gratuito",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 3,
				"evaluation" => 5,
				"img" => "recibo-teste.png",
				"other" => "R$ 15,00",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 5,
				"evaluation" => 4,
				"img" => "figma_recibo-1.png",
				"other" => "LocalWeb",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 201,
				"evaluation" => 5,
				"img" => "recibo-3.jpg",
				"other" => "<i class='fas fa-clipboard-check success-card'></i>",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 1,
				"evaluation" => 3,
				"img" => "recibo-4.jpg",
				"other" => "Gratuito",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 201,
				"evaluation" => 5,
				"img" => "recibo-3.jpg",
				"other" => "<i class='fas fa-clipboard-check success-card'></i>",
			],

			[
				"title" => "Recibo simples de serviços",
				"favorit" => 1,
				"evaluation" => 3,
				"img" => "recibo-4.jpg",
				"other" => "Gratuito",
			],
		];
	}
}
?>