<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Model\ProductManager;


class HomepagePresenter extends BasePresenter
{
	/* @var ProductManager */
	private $productManager;

	public function __construct(ProductManager $productManager) {
		$this->productManager = $productManager;
	}

	public function renderDefault()
	{
		$this->template->products =  $this->productManager->products;
	}
}
