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

	public function renderDefault($search = NULL)
	{
		if ($search === NULL) {
			$this->template->products = $this->productManager->products;
			$this->template->title = 'Nové produkty';
		} else {
			$this->template->products = $this->productManager->searchProduct($search);
			$this->template->title = 'Hledaný výraz: '.$search;
		}
	}
}
