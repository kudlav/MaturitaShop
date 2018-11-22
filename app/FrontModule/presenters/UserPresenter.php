<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\FrontModule\Forms\CartQuantityFormFactory;
use Nette;
use Nette\Application\UI\Form;
use App\Model\CartManager;
use App\Model\OrderManager;
use App\Model\PriceInvalidException;


class UserPresenter extends BasePresenter
{
	/**
	 * @var CartManager $cartManager
	 * @var OrderManager $orderManager
	 */
	private $cartManager, $orderManager;

	public function __construct(CartManager $cartManager, OrderManager $orderManager)
	{
		parent::__construct();

		$this->cartManager = $cartManager;
		$this->orderManager = $orderManager;
	}

	/**
	 * @return Navbar
	 */
	protected function createComponentNavbar(): Navbar
	{
		if ($this->getUser()->isLoggedIn()) {
			$items = $this->parameters['logged_menu'];
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

	/**
	 * @throws Nette\Application\AbortException
	 */
	public function renderOrders(): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in', ['state' => $this->storeRequest()]);
		}
		$this->template->items = $this->orderManager->getUserOrders($this->getUser()->id);
	}

	/**
	 * @param string $id
	 * @throws Nette\Application\AbortException
	 * @throws Nette\Application\BadRequestException
	 */
	public function renderOrder(string $id): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in', ['state' => $this->storeRequest()]);
		}
		$this->template->order = $this->orderManager->getOrder($id);
		$this->template->products = $this->orderManager->getOrderedProducts($id);
		$this->template->show_order_code = $this->parameters['product']['show_order_code'];
		if ($this->template->order['customerId'] != $this->user->id) {
			$this->error();
		}
	}

	/**
	 * @return Order
	 */
	public function createComponentOrder(): Order
	{
		$control = new Order($this->template->order, $this->template->products, $this->template->show_order_code);
		return $control;
	}

	public function renderCart(): void
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->template->show_order_code = $this->parameters['product']['show_order_code'];
			$this->template->items = $this->cartManager->getItems($this->getUser()->id);
			try {
				$this->template->total = $this->cartManager->getPrice($this->getUser()->id);
			} catch (PriceInvalidException $e) {}
		} else {
			$this->template->items = [];
		}
	}

	/**
	 * Remove selected product from cart
	 * @param string $itemId
	 * @throws Nette\Application\AbortException
	 */
	public function actionRemoveFromCart(string $itemId): void
	{
		if ($this->getUser()->isLoggedIn()) {
			if ($this->cartManager->removeItem($this->user->id, $itemId)) {
				$this->flashMessage('Položka byla z košíku odebrána');
			} else {
				$this->flashMessage('Položku nebylo možné z košíku odebrat, patrně se v něm nenachází', 'flash-error');
			}
			$this->redirect('User:cart');
		} else {
			// TODO cart for unregistred users
		}
	}

	/**
	 * Add selected product to cart
	 * @param string $itemId
	 * @param string|null $redirect
	 * @throws Nette\Application\AbortException
	 */
	public function actionAddToCart(string $itemId, ?string $redirect): void
	{
		if ($this->getUser()->isLoggedIn()) {
			if ($this->cartManager->addItem($this->user->id, $itemId)) {
				$this->flashMessage('Položka byla přidána do košíku');
			} else {
				$this->flashMessage('Položku nebylo možné přidat do košíku', 'flash-error');
			}
		} else {
			// TODO cart for unregistred users
		}
		if (isset($redirect)) {
				$this->redirect($redirect);
		}
		$this->redirect('User:cart');
	}

	/**
	 * @return Form
	 */
	public function createComponentCartQuantityForm(): Form
	{
		$form = new CartQuantityFormFactory($this->cartManager, $this);
		return $form->create($this->cartManager->getItems($this->user->id));
	}
}
