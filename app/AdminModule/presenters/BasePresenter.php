<?php

namespace App\AdminModule\Presenters;

use Nette;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	use \Nextras\Application\UI\SecuredLinksPresenterTrait;

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
			'Základní přehled' => ['Homepage:default'],
			'Zákazníci' => ['Customers:default'],
			'Objednávky' => ['Orders:default'],
			'Produkty' => ['Homepage:default'],
			'Seznam správců' => ['Homepage:default'],
			'Odhlásit se' => ['Sign:out'],
		];
		$control = new Navbar('Administrace', $items);
		return $control;
	}
}
