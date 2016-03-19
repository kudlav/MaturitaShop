<?php

namespace App\Presenters;

use Nette;
use App\Model;


class UserPresenter extends BasePresenter
{

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

	public function renderAccount()
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in', ['p' => $this->storeRequest()]);
		}
	}

}
