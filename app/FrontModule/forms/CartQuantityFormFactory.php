<?php
declare(strict_types=1);

namespace App\FrontModule\Forms;

use App\Model\CartManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\ResultSet;
use Nette\Utils\ArrayHash;


class CartQuantityFormFactory
{
	use Nette\SmartObject;

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
	 * @param iterable $items
	 * @return Form
	 */
	public function create(iterable $items): Form
	{
		$form = new Form;

		foreach ($items as $item) {
			$form->addText('i'.$item['katalogove_cislo'])
				->setValue($item['pocet_kusu'])
				->setRequired(true)
				->addRule(Form::INTEGER, 'Zadejte celé číslo, větší než 0.');
		}

		$form->addSubmit('recalc', 'Přepočítat')
			->setAttribute('class','submit');

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 * @throws Nette\Application\AbortException
	 */
	public function formSucceeded(Form $form, ArrayHash $values): void
	{
		if ($this->presenter->isAjax()) {
			if ($this->presenter->user->isLoggedIn()) { // Update database
				foreach ($values as $id => $value) {
					if ($value <= 0 || !$this->cartManager->addItem($this->presenter->user->id, substr($id, 1), (int)$value)) {
						$this->presenter->flashMessage('Chyba, množství musí být celé číslo, větší než 0.', 'flash-error');
					}
				}
				$this->presenter->redirect('User:cart');
			}
			else { // Update cookies
				$cart = [];
				foreach ($values as $id => $value) {
					$cart[substr($id, 1)] = $value;
				}
				$this->presenter->getHttpResponse()->setCookie('cart', json_encode($cart), '365 days');
				$this->presenter->redirect('User:cart');
			}
			$this->presenter->redrawControl('flashMessage');
			$this->presenter->redrawControl('cartCards');
			$this->presenter->redrawControl('total');
			$this->presenter->redrawControl('cart');
		}
	}

}
