<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Model\OrderManager;
use Nette;


class InfoPresenter extends BasePresenter
{
	/** @var  OrderManager */
	private $orderManager;

	public function __construct(OrderManager $orderManager)
	{
		parent::__construct();

		$this->orderManager = $orderManager;
	}

	public function renderNakup()
	{
		$this->template->delivery = $this->orderManager->getDelivery();
		$this->template->payment = $this->orderManager->getPayment();
	}
}
