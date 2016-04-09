<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\FrontModule\Model;
use App\FrontModule\Model\ProductManager;


class HomepagePresenter extends BasePresenter
{
	/** @var ProductManager */
	private $productManager;

	public function __construct(ProductManager $productManager) {
		parent::__construct();

		$this->productManager = $productManager;
	}

	public function renderDefault()
	{
		$this->template->products =  $this->productManager->products;
	}
}
