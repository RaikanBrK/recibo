<?php
namespace App\Tools;
use MF\Model\Container;

class Card {
	protected $cards;
	protected $query;

	public function __get($attr) {
		return $this->$attr;
	}

	public function __set($attr, $value) {
		return $this->$attr = $value;
	}

	public function gerarCards() {
		$cards = $this->__get('cards');
		$query = "";

		forEach($cards as $indice => $card) {
			$query .= "
				<div class='col-sm-6 col-lg-4 col-xl-3'>
					<div class='card-item'>
						<img src='/img/" . $card['img'] . "' alt='recibo-1' class='card-img light-zoom'>
						<div class='card-item-body'>
							<div class='card-item-category'>
								<a href='/modelos_recibo?category=underline'>Underline,</a>
								<a href='/modelos_recibo?category=logo'>Com Logo</a>
							</div>

							<div class='card-item-desc'>
								<a href='/teste' class='card-item-title'>" . $card['title'] . "</a>
							</div>
						</div>

						<div class='card-item-footer'>
							<div class='icons'>
								<div class='evaluation'>
									<i class='fas fa-star icon-suport'></i>
									<span class='number-suport-icon'>" . $card['evaluation'] . "</span>
								</div>
								<div class='favorit'>
									<i class='far fa-heart icon-suport'></i>
									<span class='number-suport-icon'>" . $card['favorit'] . "</span>
								</div>
							</div>

							<div class='other'>
								<a href='/' class='other-title'>" . $card['other'] . "</a>
							</div>
						</div>
					</div>
				</div>
			";
			//<i class="far fa-star"></i>
			// <i class="fas fa-heart"></i>
		}
		$this->query = $query;
	}

	public function renderCards() {
		if (is_null($this->query)) {
			$this->gerarCards();
		}
		
		echo $this->query;
	}

}
?>