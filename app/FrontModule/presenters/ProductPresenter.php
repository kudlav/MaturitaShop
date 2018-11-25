<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette;
use App\FrontModule\Forms\BuyFormFactory;
use App\FrontModule\Forms\ContactFormFactory;
use App\Model\ProductManager;
use App\Model\CartManager;
use App\Model\OrderManager;
use App\Model\ParameterManager;
use Nette\Application\UI\Form;


class ProductPresenter extends BasePresenter
{
	/**
	 * @var BuyFormFactory $buyFormFactory
	 * @var ProductManager $productManager
	 * @var CartManager $cartManager
	 * @var OrderManager $orderManager
	 * @var ParameterManager $parameterManager
	 */
	private $buyFormFactory, $productManager, $cartManager, $orderManager, $parameterManager;

	public function __construct(BuyFormFactory $buyFormFactory, ProductManager $productManager, CartManager $cartManager, OrderManager $orderManager, ParameterManager $parameterManager)
	{
		parent::__construct();

		$this->buyFormFactory = $buyFormFactory;
		$this->productManager = $productManager;
		$this->cartManager = $cartManager;
		$this->orderManager = $orderManager;
		$this->parameterManager = $parameterManager;
	}

	/**
	 * @param string $id
	 * @throws Nette\Application\BadRequestException
	 */
	public function renderDefault(string $id): void
	{
		$this->template->product = $this->productManager->getItem($id);
		if ($this->template->product === NULL) {
			$this->error('Požadovaný produkt neexistuje');
		}

		$this->template->productParameters = $this->parameterManager->getProductParameters($id);
		$this->template->productPhotos = explode(';', $this->template->product->fotografie ?? '');
		$this->template->product_parameters = $this->parameters['product'];
	}

	/**
	 * @return Form
	 */
	public function createComponentContactForm(): Form
	{
		$operator_email = $this->parameters['contact']['email_from'];
		$form = new ContactFormFactory($operator_email, $this->presenter);
		return $form->create();
	}

	/**
	 * @param $back
	 * @throws Nette\Application\AbortException
	 */
	public function renderBuy($back): void
	{
		if (!$this->getUser()->isLoggedIn()) {
			$this->flashMessage('Přihlaste se prosím.');
			$this->redirect('Sign:in', ['state' => $this->storeRequest()]);
		}

		if (isset($back)) {
			$session = $this->getSession('buy');
			$session->back = TRUE;
		}
		$this->template->heading = "Vaše objednávka:";
		$this->template->items = ['Doprava a platba', 'Souhrn objednávky'];
		$this->template->phase = $this->orderManager->detectPurchasePhase($this->getSession('buy'));
	}

	/**
	 * @return Buy|Form
	 */
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
			$form = new Buy($this->getSession('buy'), $this->user, $this->cartManager, $this->orderManager);
		}
		return $form;
	}

	/**
	 * @throws Nette\Application\AbortException
	 */
	public function actionSubmitOrder(): void
	{
		$session = $this->getSession('buy');
		$phase = $this->orderManager->detectPurchasePhase($session);

		if ($phase){
			$userId = $this->getUser()->id;
			$items = $this->cartManager->getItems($userId);

			try {
				$order = ($this->orderManager->orderProducts($items, $session, $userId));
				if ($order !== -1) {
					$session->remove();
					$this->flashMessage("Objednávka $order byla úspěšně vytvořena");
				} else {
					$this->flashMessage('Objednávku nebylo možné vytvořit!', 'flash-error');
				}
			} catch (\Exception $e) {
				\Tracy\Debugger::barDump($e);
				$this->flashMessage('Objednávku nebylo možné vytvořit!', 'flash-error');
			}

		} else {
			$this->flashMessage('Před objednáním je nutné projít celým objednávkovým procesem!','flash-error');
		}
		$this->redirect('Homepage:');
	}
}
