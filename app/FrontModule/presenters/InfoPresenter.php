<?php

namespace App\FrontModule\Presenters;

use App\Model\OrderManager;
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

	public function renderKontakty()
	{
		$this->template->contact = $this->parameters['contact'];
	}

	/**
	 * @return Navbar
	 */
	protected function createComponentNavbar()
	{
		$items = [
			'Kontakty' => ['Info:kontakty'],
			'Obchodní podmínky' => ['Info:podminky'],
			'Doprava a platba' => ['Info:nakup'],
		];
		$control = new Navbar('Nakupování', $items);
		return $control;
	}
}
