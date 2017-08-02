<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\FrontModule\Model;
use App\FrontModule\Model\CartManager;
use App\FrontModule\Model\ProductManager;
use App\FrontModule\Forms\SearchFormFactory;
use App\FrontModule\Model\Parameters;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	/** @var Parameters */
	protected $parameters;

	public function injectParameters(Parameters $parameters) {
		$this->parameters = $parameters->getParam();
	}

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
		$this->template->phone = $this->parameters['contact']['phone'];
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
		$items = $this->parameters['category_menu'];
		$control = new Navbar('Kategorie', $items, 'cat');
		return $control;
	}
}
