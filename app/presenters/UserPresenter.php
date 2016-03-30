<?php

namespace App\Presenters;

use Nette;
use App\Model;
use App\Model\CartManager;


class UserPresenter extends BasePresenter
{
	/** @var CartManager */
	private $cartManager;

	public function injectCartManager(CartManager $cartManager)
	{
		parent::injectCartManager($this->cartManager = $cartManager);
	}


	protected function createComponentNavbar()
	{
		if ($this->getUser()->isLoggedIn()) {
			$items = [
				'Košík' => ['User:cart'],
				'Objednávky' => ['User:orders'],
				'Odhlásit se' => ['Sign:out'],
			];
		} else {
			$items = [
				'Košík' => ['User:cart'],
				'Přihlásit se' => ['Sign:in', $this->storeRequest()],
				'Zaregistrovat se' => ['Register:default'],
			];
		}
		$control = new Navbar('Můj účet', $items);
		return $control;
	}

	public function renderOrders()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in', ['p' => $this->storeRequest()]);
		}
	}

	public function renderCart()
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->template->items = $this->cartManager->getItems($this->getUser()->id);
			$this->template->total = $this->cartManager->getPrice($this->getUser()->id);
		} else {
			$this->template->items = [];
			$this->template->total = 0;
		}
	}

}
