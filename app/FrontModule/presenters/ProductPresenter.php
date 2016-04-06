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
	}

	public function renderBuy($back)
	{
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
		$phase = $this->orderManager->detectPurchasePhase($session);
		if (!$phase) {
			$form = $this->buyFormFactory->createForm();
			$form->onSuccess[] = function(){
				$this->redirect('Product:buy');
			};
		} else {
			$form = new Buy($this->getSession('buy'), $this->user, $this->cartManager);
		}
		return $form;
	}

	public function actionSubmitOrder(){
		$session = $this->getSession('buy');
		$phase = $this->orderManager->detectPurchasePhase($session);

		if ($phase){
			$userId = $this->getUser()->id;
			$items = $this->cartManager->getItems($userId);

			$order = ($this->orderManager->orderProducts($items, $session, $userId));
			if ($order) {
				$session->remove();
				$this->flashMessage('Objednávka č.' . sprintf("%05d", $order) . ' byla úspěšně vytvořena');
				$this->redirect('Homepage:');
			}
			$this->flashMessage('Objednávku nebylo možné vytvořit!', 'flash-error');
			$this->redirect('Homepage:');
		} else {
			$this->flashMessage('Před objednáním je nutné projít celým objednávkovým procesem!','flash-error');
			$this->redirect('Homepage:');
		}
	}
}
