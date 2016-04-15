<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\FrontModule\Model;
use App\FrontModule\Model\CartManager;
use App\FrontModule\Model\ProductManager;
use App\FrontModule\Forms\SearchFormFactory;


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

	/** @var  ProductManager */
	private $productManager;

	public function injectProductManager(ProductManager $productManager)
	{
		$this->productManager = $productManager;
	}

	protected function startup()
	{
		parent::startup();

		$this->user->getStorage()->setNamespace('Front');

		$this->template->page = $this->getName();
		$this->template->cartCount = 1;
		$this->template->cartPrice = 3600;

	}

	protected function createComponentSearch()
	{
		$form = new SearchFormFactory($this->productManager, $this);
		return $form->create();
	}

	protected function createComponentCart()
	{
		$user = $this->getUser();
		$control = new Cart($user, $this->cartManager);
		return $control;
	}

	protected function createComponentNavbar()
	{
		$items = [
			'Kontakty' => ['Info:kontakty'],
			'Obchodní podmínky' => ['Info:podminky'],
			'Doprava a platba' => ['Info:nakup'],
		];
		$control = new Navbar('Nakupování', $items);
		return $control;
	}
}
