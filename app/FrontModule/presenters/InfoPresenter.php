<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette;
use App\Model\OrderManager;


class InfoPresenter extends BasePresenter
{
	/**
	 * @var OrderManager $orderManager
	 */
	private $orderManager;

	public function __construct(OrderManager $orderManager)
	{
		parent::__construct();

		$this->orderManager = $orderManager;
	}

	public function renderNakup(): void
	{
		$this->template->delivery = $this->orderManager->getDelivery();
		$this->template->payment = $this->orderManager->getPayment();
	}

	public function renderKontakty(): void
	{
		$this->template->contact = $this->parameters['contact'];
	}

	/**
	 * @return Navbar
	 */
	protected function createComponentNavbar(): Navbar
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
