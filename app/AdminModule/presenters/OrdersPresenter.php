<?php

namespace App\AdminModule\Presenters;

use Nette;
use App\AdminModule\Model\OrderManager;
use App\FrontModule\Model\ProductManager;


class OrdersPresenter extends BasePresenter
{
	/**
	 * @var OrderManager $orderManager
	 * @var ProductManager $productManager
	 */
	private $orderManager;
	private $productManager;

	public function __construct(OrderManager $orderManager, ProductManager $productManager)
	{
		parent::__construct();

		$this->orderManager = $orderManager;
		$this->productManager = $productManager;
	}

	public function renderDefault()
	{
		$this->template->orders = $this->orderManager->getOrdersInProgress();
		$this->template->states = ['čeká na vyřízení', 'zboží odesláno', 'připraveno k vyzvednutí', 'objednávka vyřízena', 'objednávka zrušena'];
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
	}

	public function renderDetail($id,$key)
	{
		$this->template->order = $this->orderManager->getOrder($id);
		$this->template->producs = $this->productManager($this->template->order['customer']);
	}

	public function renderEdit()
	{

	}
}
