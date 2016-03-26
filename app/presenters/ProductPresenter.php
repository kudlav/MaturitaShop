<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Model\ProductManager;


class ProductPresenter extends BasePresenter
{
	private $productManager;

	public function injectProductManager(ProductManager $productManager) {
		$this->productManager = $productManager;
	}

	public function renderDefault($id, $produkt)
	{
		$this->template->product = $this->productManager->getItem($id);
		if ($this->template->product === NULL) {
			$this->error('Požadovaný produkt neexistuje');
		}
	}


}
