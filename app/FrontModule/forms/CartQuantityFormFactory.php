<?php

namespace App\FrontModule\Forms;

use App\FrontModule\Model\CartManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;


class CartQuantityFormFactory extends Nette\Object
{

	/**
	 * @var CartManager $cartManager
	 * @var Presenter $presenter
	 */
	private $cartManager, $presenter;


	public function __construct(CartManager $cartManager, Presenter $presenter)
	{
		$this->cartManager = $cartManager;
		$this->presenter = $presenter;
	}


	/**
	 * @param array $items
	 * @return Form
	 */
	public function create($items)
	{
		$form = new Form;

		foreach ($items as $item) {
			$form->addText('i'.$item['id'])
				->setValue($item['count'])
				->setType('number');
		}

		$form->addSubmit('recalc', 'Přepočítat')
			->setAttribute('class','submit');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}


	/**
	 * @param Form $form
	 * @param array $values
	 */
	public function formSucceeded(Form $form, $values)
	{
		if ($this->presenter->isAjax()) {
			foreach ($values as $id => $value) {
				if(!$this->cartManager->addItem($this->presenter->user->id, preg_replace('/i/', '', $id), $value)){
					$this->presenter->flashMessage('Chyba, množství musí být celé číslo, větší než 1.','flash-error');
				}
			}
			$this->presenter->redrawControl('flashMessage');
			$this->presenter->redrawControl('cartCards');
			$this->presenter->redrawControl('total');
			$this->presenter->redrawControl('cart');
		}
	}

}
