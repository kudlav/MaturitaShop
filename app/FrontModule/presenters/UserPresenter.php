<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\FrontModule\Forms\CartQuantityFormFactory;
use App\Model\ProductManager;
use App\Model\SupplierManager;
use Nette;
use Nette\Application\UI\Form;
use App\Model\CartManager;
use App\Model\OrderManager;


class UserPresenter extends BasePresenter
{
	/**
	 * @var CartManager $cartManager
	 * @var OrderManager $orderManager
	 * @var ProductManager $productManager
	 */
	private $cartManager, $orderManager, $productManager;

	public function __construct(CartManager $cartManager, OrderManager $orderManager, ProductManager $productManager)
	{
		parent::__construct();

		$this->cartManager = $cartManager;
		$this->orderManager = $orderManager;
		$this->productManager = $productManager;
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
		$this->template->delivery = $this->orderManager->getDelivery();
		$this->template->payment = $this->orderManager->getPayment();
	}

	/**
	 * @param int $id
	 * @throws Nette\Application\AbortException
	 * @throws Nette\Application\BadRequestException
	 */
	public function renderOrder(int $id): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->redirect('Sign:in', ['state' => $this->storeRequest()]);
		}
		$this->template->order = $this->orderManager->getOrder($id);
		if ($this->template->order['customerId'] != $this->user->id) {
			$this->error();
		}
		$this->template->products = $this->orderManager->getOrderedProducts($id);
		$this->template->total = $this->template->order['deliveryPrice'] + $this->template->order['paymentPrice'];
		foreach ($this->template->products as $product) {
			$this->template->total += $product['price'] * $product['quantity'];
		}
	}

	/**
	 * @return Order
	 */
	public function createComponentOrder(): Order
	{
		$control = new Order($this->template->order, $this->template->products, $this->template->total);
		return $control;
	}

	public function actionCart(): void
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->template->items = $this->cartManager->getItems($this->getUser()->id)->fetchAll();
			$this->template->total = $this->cartManager->getPrice($this->getUser()->id);
		} else {
			$this->template->items = [];
			$this->template->total = 0;
			$cartCookies = $this->getHttpRequest()->getCookie('cart');
			if ($cartCookies !== null) {
				$cart = json_decode($cartCookies, true);
				foreach ($cart as $id => $count) {
					$product = $this->productManager->getItem($id);
					if ($product !== null AND $product->zobrazovat == 1) {
						$this->template->items[] = [
							ProductManager::COLUMN_ID => $product->katalogove_cislo,
							ProductManager::COLUMN_NAME => $product->nazev,
							ProductManager::COLUMN_DESCRIPTION => $product->popis,
							ProductManager::COLUMN_PRICE => $product->cena,
							ProductManager::COLUMN_QUANTITY => $product->mnozstvi_skladem,
							ProductManager::COLUMN_PHOTO => $product->fotografie,
							ProductManager::COLUMN_CATEGORY => $product->kategorie,
							ProductManager::COLUMN_SHOW => $product->zobrazovat,
							'pocet_kusu' => $count,
                            'productDelivery' => $product->ref(SupplierManager::TABLE_NAME, ProductManager::COLUMN_SUPPLIER)->dodaci_lhuta,
						];
						$this->template->total += $product->cena * $count;
					}
					else {
						unset($cart[$id]);
					}
				}
				$this->getHttpResponse()->setCookie('cart', json_encode($cart), '365 days');
			}
		}
	}

	/**
	 * @return Form
	 */
	public function createComponentCartQuantityForm(): Form
	{
		$form = new CartQuantityFormFactory($this->cartManager, $this);
		return $form->create($this->template->items);
	}

	/**
	 * Remove selected product from cart
	 * @param string $itemId
	 * @throws Nette\Application\AbortException
	 */
	public function actionRemoveFromCart(string $itemId): void
	{
		if ($this->getUser()->isLoggedIn()) { // Update database
			if ($this->cartManager->removeItem($this->user->id, $itemId)) {
				$this->flashMessage('Položka byla z košíku odebrána');
				$this->redirect('User:cart');
			}
		}
		else { // Update user cookies
			$cartCookies = $this->getHttpRequest()->getCookie('cart');
			if ($cartCookies !== null) {
				$cart = json_decode($cartCookies, true);
				if (isset($cart[$itemId])) {
					unset($cart[$itemId]);
					$this->getHttpResponse()->setCookie('cart', json_encode($cart), '365 days');
					$this->flashMessage('Položka byla z košíku odebrána');
					$this->redirect('User:cart');
				}
			}
		}
		$this->flashMessage('Položku nebylo možné z košíku odebrat, patrně se v něm nenachází', 'flash-error');
		$this->redirect('User:cart');
	}

	/**
	 * Add selected product to cart
	 * @param string $itemId
	 * @param string|null $redirect
	 * @throws Nette\Application\AbortException
	 */
	public function actionAddToCart(string $itemId, ?string $redirect): void
	{
		if (!isset($redirect)) {
			$redirect = 'User:cart';
		}

		$product = $this->productManager->getItem($itemId);
		if ($product === null OR $product->zobrazovat == 0) {
			$this->flashMessage('Položku nebylo možné přidat do košíku', 'flash-error');
			$this->redirect($redirect);
		}

		if ($this->getUser()->isLoggedIn()) { // Update database
			if (!$this->cartManager->addItem($this->user->id, $itemId)) {
				$this->flashMessage('Položku nebylo možné přidat do košíku', 'flash-error');
				$this->redirect($redirect);
			}
		}
		else { // Update user cookies
			$cartCookies = $this->getHttpRequest()->getCookie('cart', '{}');
			$cart = json_decode($cartCookies, true);
			if (isset($cart[$itemId])) {
				$cart[$itemId]++;
			}
			else {
				$cart[$itemId] = 1;
			}
			$this->getHttpResponse()->setCookie('cart', json_encode($cart), '365 days');
		}
		$this->flashMessage('Položka byla přidána do košíku');
		$this->redirect($redirect);
	}

	/**
	 * @param int $id
	 * @throws Nette\Application\AbortException
	 * @throws Nette\Application\BadRequestException
	 */
	public function renderInvoice(int $id): void
	{
		if (!$this->user->isLoggedIn()) {
			$this->user->getStorage()->setNamespace('Admin');
			if (!$this->user->isLoggedIn()) {
				$this->user->getStorage()->setNamespace('Front');
				$this->redirect('Sign:in', ['state' => $this->storeRequest()]);
			}
		}
		$this->template->order = $this->orderManager->getOrder($id);

		$this->user->getStorage()->setNamespace('Admin');
		if (!($this->user->isInRole('prodejce') OR $this->user->isInRole('spravce'))) {
			$this->user->getStorage()->setNamespace('Front');
			if ($this->template->order['customerId'] != $this->user->id) {
				$this->error();
			}
		}

		$this->template->products = $this->orderManager->getOrderedProducts($id);
		$this->template->total = $this->template->order['deliveryPrice'] + $this->template->order['paymentPrice'];
		foreach ($this->template->products as $product) {
			$this->template->total += $product['price'] * $product['quantity'];
		}
		$this->template->contact = $this->parameters['contact'];
		$this->user->getStorage()->setNamespace('Front');
	}
}
