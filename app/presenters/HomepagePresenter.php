<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Model\ProductManager;


class HomepagePresenter extends BasePresenter
{
	private $productManager;

	public function injectProductManager(ProductManager $productManager) {
		$this->productManager = $productManager;
	}

	public function renderDefault()
	{
		$this->template->products =  $this->productManager->products;
	}

	protected function createComponentNavbar()
	{
		$items = [
			'Citroen' => ['Homepage:'],
			'Peugeot' => ['Homepage:'],
			'Fiat' => ['Homepage:'],
		];
		$control = new Navbar('Kategorie', $items);
		return $control;
	}
}
