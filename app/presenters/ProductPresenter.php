<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Forms\BuyFormFactory;
use App\Model\ProductManager;


class ProductPresenter extends BasePresenter
{
	/** @var BuyFormFactory */
	private $buyFormFactory;

	/** @var ProductManager */
	private $productManager;

	public function injectBuyFormFactory(BuyFormFactory $buyFormFactory) {
		$this->buyFormFactory = $buyFormFactory;
	}

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

	public function renderBuy($values)
	{
		$this->template->title = "Vaše objednávka:";
		$this->template->items = [
			'Doprava a platba',
			'Osobní informace',
			'Souhrn objednávky',
		];
	}

	public function createComponentBuyForm()
	{
		$form = $this->buyFormFactory->createForm();
		return $form;
	}

}
