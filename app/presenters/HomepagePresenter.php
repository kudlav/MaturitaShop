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
}
