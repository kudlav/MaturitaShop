<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\ContactFormFactory;
use Nette;
use App\AdminModule\Forms\ChangeStateFormFactory;
use App\FrontModule\Presenters\Order;
use App\FrontModule\Model\OrderManager;
use App\FrontModule\Model\ProductManager;
use App\FrontModule\Model\UserManager;


class OrdersPresenter extends BasePresenter
{
	private $userId;

	/**
	 * @var OrderManager $orderManager
	 * @var ProductManager $productManager
	 * @var UserManager $userManager
	 */
	private $orderManager, $productManager, $userManager;

	public function __construct(OrderManager $orderManager, ProductManager $productManager, UserManager $userManager)
	{
		parent::__construct();

		$this->orderManager = $orderManager;
		$this->productManager = $productManager;
		$this->userManager = $userManager;
	}

	public function renderDefault()
	{
		$this->template->orders = $this->orderManager->getOrdersInProgress();
		$this->template->states = $this->orderManager->getStates();
	}

	public function createComponentChangeStateForm()
	{
		$form = new ChangeStateFormFactory($this, $this->orderManager, $this->orderManager->getOrdersInProgress(), $this->orderManager->getStates());
		return $form->create();
	}

	/**
	 * @secured
	 */
	public function handleDelete($id)
	{
		if ($this->orderManager->deleteOrder($id)) {
			$this->flashMessage('Objednávka byla odstraněna');
		} else {
			$this->flashMessage('Objednávku nebylo možné odstranit.','flash-error');
		}
		$this->redirect('Orders:');
	}

	public function renderDetail($id)
	{
		$this->template->order = $this->orderManager->getOrder($id);
		$this->template->products = $this->orderManager->getOrderedProducts($id);
	}

	public function createComponentOrder()
	{
		$control = new Order($this->template->order, $this->template->products);
		return $control;
	}

	public function renderEdit()
	{

	}

	public function renderKontakt($id)
	{
		$this->userId = $id;
	}

	public function createComponentContact()
	{
		$control = new Contact($this->userManager->getContact($this->userId), $this->parameters['contact']['email_from']);
		return $control;
	}
}
