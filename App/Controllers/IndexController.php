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
			[
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
			]
		);
		$this->view->card->gerarCards();

		$this->render('dashboard');
	}
}
?>