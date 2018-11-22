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
	 * @var int $userId
	 */
	private $orderManager, $productManager, $userManager, $userId;

	public function __construct(OrderManager $orderManager, ProductManager $productManager, UserManager $userManager)
	{
		parent::__construct();

		$this->orderManager = $orderManager;
		$this->productManager = $productManager;
		$this->userManager = $userManager;
	}

	public function renderDefault(): void
	{
		$this->template->orders = $this->orderManager->getOrdersInProgress();
		$this->template->states = $this->orderManager->getStates();
	}

	public function createComponentChangeStateForm(): Form
	{
		$form = new ChangeStateFormFactory($this, $this->orderManager, $this->orderManager->getOrdersInProgress(), $this->orderManager->getStates());
		return $form->create();
	}

	/**
	 * @secured
	 * @param string $id
	 * @throws Nette\Application\AbortException
	 */
	public function handleDelete(string $id): void
	{
		if ($this->orderManager->deleteOrder($id)) {
			$this->flashMessage('Objednávka byla odstraněna');
		} else {
			$this->flashMessage('Objednávku nebylo možné odstranit.','flash-error');
		}
		$this->redirect('Orders:');
	}

	/**
	 * @param string $id
	 */
	public function renderDetail(string $id): void
	{
		$this->template->order = $this->orderManager->getOrder($id);
		$this->template->products = $this->orderManager->getOrderedProducts($id);
		$this->template->show_order_code = $this->parameters['product']['show_order_code'];
	}

	public function createComponentOrder(): Order
	{
		$control = new Order($this->template->order, $this->template->products, $this->template->show_order_code);
		return $control;
	}

	public function renderEdit(): void
	{

	}

	public function renderContact(int $id): void
	{
		$this->userId = $id;
	}

	public function createComponentContact(): Contact
	{
		$control = new Contact($this->userManager->getContact($this->userId), $this->parameters['contact']['email_from']);
		return $control;
	}
}
