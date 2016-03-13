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
				'Registrační údaje' => ['User:account'],
				'Odhlásit se' => ['Sign:out'],
			];
		} else {
			$items = [
				'Košík' => ['User:cart'],
				'Přihlásit se' => ['Sign:in', $this->storeRequest()],
			];
		}

		$control = new Navbar('Můj účet', $items);
		return $control;
	}

}
