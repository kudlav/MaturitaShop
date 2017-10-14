<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\FrontModule\Model;
use App\FrontModule\Forms\BuyFormFactory;
use App\FrontModule\Model\ProductManager;
use App\FrontModule\Model\CartManager;
use App\FrontModule\Model\OrderManager;


class ProductPresenter extends BasePresenter
{
	/**
	 * @var BuyFormFactory $buyFormFactory
	 * @var ProductManager $productManager
	 * @var CartManager $cartManager
	 * @var OrderManager $orderManager
	 */
	private $buyFormFactory, $productManager, $cartManager, $orderManager;


	public function __construct(BuyFormFactory $buyFormFactory, ProductManager $productManager, CartManager $cartManager, OrderManager $orderManager)
	{
		parent::__construct();

		$this->buyFormFactory = $buyFormFactory;
		$this->productManager = $productManager;
		$this->cartManager = $cartManager;
		$this->orderManager = $orderManager;
	}

	public function renderDefault($id, $produkt)
	{
		$this->template->product = $this->productManager->getItem($id);
		if ($this->template->product === NULL) {
			$this->error('Požadovaný produkt neexistuje');
		}
		$this->template->categories = implode(' &gt; ', $this->productManager->getCategoryTree($this->template->product['category'], $this->template->baseUrl));
		$this->template->productPhotos = explode(';', $this->template->product['photo']);
		$this->template->product_parameters = $this->parameters['product'];
		$this->template->eshop = $this->parameters['eshop'];
	}

	public function renderBuy($back)
	{
		if (!$this->parameters['eshop']) {
			$this->error(); //Error 404
		}

		if (!$this->getUser()->isLoggedIn()) {
			$this->flashMessage('Přihlaste se prosím.');
			$this->redirect('Sign:in', ['state' => $this->storeRequest()]);
		}

		if (isset($back)) {
			$session = $this->getSession('buy');
			$session->back = TRUE;
		}
		$this->template->title = "Vaše objednávka:";
		$this->template->items = ['Doprava a platba', 'Souhrn objednávky'];
		$this->template->phase = $this->orderManager->detectPurchasePhase($this->getSession('buy'));
	}

	public function createComponentBuyForm()
	{
		$session =  $this->getSession('buy');

		if (isset($session->delivery) && !array_key_exists($session->delivery, $this->orderManager->getDelivery())) {
			unset($session->delivery);
		}
		if (isset($session->payment) && !array_key_exists($session->payment, $this->orderManager->getPayment())) {
			unset($session->payment);
		}

		$phase = $this->orderManager->detectPurchasePhase($session);
		if (!$phase) {
			$form = $this->buyFormFactory->createForm();
			$form->onSuccess[] = function(){
				$this->redirect('Product:buy');
			};
		} else {
			$form = new Buy($this->getSession('buy'), $this->user, $this->cartManager, $this->orderManager, $this->parameters['product']['show_order_code']);
		}
		return $form;
	}

	public function actionSubmitOrder(){
		if (!$this->parameters['eshop']) {
			$this->error(); //Error 404
		}	

		$session = $this->getSession('buy');
		$phase = $this->orderManager->detectPurchasePhase($session);

		if ($phase){
			$userId = $this->getUser()->id;
			$items = $this->cartManager->getItems($userId);

			try {
				$order = ($this->orderManager->orderProducts($items, $session, $userId));
				if ($order) {
					$session->remove();
					$this->flashMessage('Objednávka č.' . sprintf("%05d", $order) . ' byla úspěšně vytvořena');
				} else {
					$this->flashMessage('Objednávku nebylo možné vytvořit!', 'flash-error');
				}
			} catch (\Exception $e) {
				$this->flashMessage('Objednávku nebylo možné vytvořit! '.$e->getMessage(), 'flash-error');
			}

		} else {
			$this->flashMessage('Před objednáním je nutné projít celým objednávkovým procesem!','flash-error');
		}
		$this->redirect('Homepage:');
	}
}
