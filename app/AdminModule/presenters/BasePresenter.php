<?php

namespace App\AdminModule\Presenters;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	protected function startup()
	{
		parent::startup();

		$this->user->getStorage()->setNamespace('Admin');

		if ($this->name != "Admin:Sign" && !$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		} elseif ($this->user->isLoggedIn() && !$this->user->isInRole('admin')) {
			$this->redirect(':Front:Sign:out');
		}
	}

	protected function createComponentNavbar()
	{
		$items = [
			'Základní přehled' => ['Homepage:'],
			'Zákazníci' => ['Customers:'],
			'Objednávky' => ['Orders:'],
			'Produkty' => ['Homepage:'],
			'Seznam správců' => ['Homepage:'],
			'Odhlásit se' => ['Sign:out'],
		];
		$control = new Navbar('Administrace', $items);
		return $control;
	}
}
