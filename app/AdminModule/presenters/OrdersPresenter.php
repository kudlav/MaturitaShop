<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;
use App\AdminModule\Forms\ChangeStateFormFactory;
use App\FrontModule\Presenters\Order;
use App\Model\OrderManager;
use App\Model\ProductManager;
use App\Model\UserManager;
use Nette\Application\UI\Form;


class OrdersPresenter extends BasePresenter
{

	/**
	 * @var OrderManager $orderManager
	 * @var ProductManager $productManager
	 * @var UserManager $userManager
	 * @var int $orderId
	 */
	private $orderManager, $productManager, $userManager, $orderId;

	public function __construct(OrderManager $orderManager, ProductManager $productManager, UserManager $userManager)
	{
		parent::__construct();

		$this->orderManager = $orderManager;
		$this->productManager = $productManager;
		$this->userManager = $userManager;
	}

	public function renderDefault(): void
	{
		$this->template->orders = $this->orderManager->getOrdersByState('in progress');
		$this->template->states = $this->orderManager->getStates();
	}

	public function createComponentChangeStateForm(): Form
	{
		$form = new ChangeStateFormFactory($this, $this->orderManager, $this->orderManager->getOrdersByState('in progress'), $this->orderManager->getStates());
		return $form->create();
	}

	/**
	 * @secured
	 * @param int $id
	 * @throws Nette\Application\AbortException
	 */
	public function handleDelete(int $id): void
	{
		if ($this->orderManager->deleteOrder($id)) {
			$this->flashMessage('Objednávka byla odstraněna');
		} else {
			$this->flashMessage('Objednávku nebylo možné odstranit.','flash-error');
		}
		$this->redirect('Orders:');
	}

	/**
	 * @param int $id
	 */
	public function renderDetail(int $id): void
	{
		$this->template->order = $this->orderManager->getOrder($id);
		$this->template->products = $this->orderManager->getOrderedProducts($id);
		$this->template->total = $this->template->order['deliveryPrice'] + $this->template->order['paymentPrice'];
		foreach ($this->template->products as $product) {
			$this->template->total += $product['price'] * $product['quantity'];
		}
	}

	public function createComponentOrder(): Order
	{
		$control = new Order($this->template->order, $this->template->products, $this->template->total);
		return $control;
	}

	public function renderEdit(): void
	{

	}

	public function actionContact(int $id): void
	{
		$this->orderId = $id;
	}

	public function createComponentContact(): Contact
	{
		$control = new Contact($this->orderManager->getOrder($this->orderId), $this->parameters['contact']['email_from']);
		return $control;
	}
}
