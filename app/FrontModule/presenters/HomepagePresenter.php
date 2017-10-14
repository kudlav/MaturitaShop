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

	public function renderDefault($search = NULL, $cat = NULL)
	{
		$this->template->eshop = $this->parameters['eshop'];

		if ($search !== NULL) {
			$this->template->products = $this->productManager->searchProduct($search, $this->parameters['fulltext_search']);
			$this->template->title = 'Hledaný výraz: '.htmlspecialchars($search);
		}
		elseif ($cat !== NULL) {
			$category = $this->productManager->getCategory($cat);
			if ($category === NULL) {
				$this->error('Požadovaná kategorie neexistuje');
			}
			$this->template->products = $this->productManager->getProducts($cat);
			$this->template->title = 'Kategorie: '.implode(' &gt; ', $this->productManager->getCategoryTree($cat, $this->template->baseUrl));
		}
		else {
			$this->template->products = $this->productManager->products;
			$this->template->title = 'Nové produkty';
		}
	}
}
