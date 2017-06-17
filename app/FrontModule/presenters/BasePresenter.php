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

	/**
	 * @return Navbar
	 */
	protected function createComponentNavbar()
	{
		$items = [
			'Světla, blikače' => ['Homepage:default', '0'],
			'<span class="subitem">Tajné</span>' => ['Homepage:default', '2'],
			'<span class="subitem">Ostatní</span>' => ['Homepage:default', '1'],
		];
		$control = new Navbar('Kategorie', $items, 'cat');
		return $control;
	}
}
