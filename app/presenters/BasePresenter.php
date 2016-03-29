<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use App\Model\CartManager;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	/** @var CartManager */
	private $cartManager;

	public function injectCartManager(CartManager $cartManager)
	{
		$this->cartManager = $cartManager;
	}

	protected function startup()
	{
		parent::startup();
		$this->template->page = $this->getName();

		$this->template->cartCount = 1;
		$this->template->cartPrice = 3600;

	}

	protected function createComponentSearch()
	{
		$form = new Form();

		$form->addText('searchTerm')
			->setType('search')
			->setAttribute('placeholder', 'Hledaný produkt...');

		$form->addSubmit('send','Hledat')
			->setAttribute('class', 'button');

		$form->onSuccess[] = array($this, 'postFormSucceeded');

		return $form;
	}

	protected function createComponentCart()
	{
		$user = $this->getUser();
		$control = new Cart($user, $this->cartManager);
		return $control;
	}

}
