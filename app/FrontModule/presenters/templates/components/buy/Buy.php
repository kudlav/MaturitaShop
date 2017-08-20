<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Model\OrderManager;
use App\FrontModule\Model\PriceInvalidException;
use Nette\Application\UI\Control;
use Nette\Http\SessionSection;
use App\FrontModule\Model\CartManager;
use Nette\Security\User;


class Buy extends Control
{
	/**
	 * @var SessionSection $session
	 * @var User $user
	 * @var CartManager $cartManager
	 * @var OrderManager $orderManager
	 * @var bool $show_order_code
	 * */
	private $session, $user, $cartManager, $orderManager, $show_order_code;

	public function  __construct(SessionSection $session, User $user, CartManager $cartManager, OrderManager $orderManager, bool $show_order_code)
	{
		parent::__construct();

		$this->session = $session;
		$this->user = $user;
		$this->cartManager = $cartManager;
		$this->orderManager = $orderManager;
		$this->show_order_code = $show_order_code;
	}

	public function render()
	{

		$userId = $this->user->id;
		$template = $this->template;

		$template->setFile(__DIR__ . '/buy.latte');
		$template->items = $this->cartManager->getItems($userId);
		$template->show_order_code = $this->show_order_code;

		$delivery = $this->orderManager->getDelivery(TRUE);
		$payment = $this->orderManager->getPayment(TRUE);

		$template->form = [];
		$template->form['Vaše poznámka'] = ['name' => $this->session->note];
		if ($this->session->delivery != NULL) {
			$template->form['Doručení'] = $delivery[$this->session->delivery];
		}
		if ($this->session->payment != NULL) {
			$template->form['Platba'] = $payment[$this->session->payment];
		}

		$template->total = NULL;
		// Get price of cart
		try {
			$template->total = $this->cartManager->getPrice($userId);
			// Get price of delivery and payment
			foreach ($template->form as $item => $value) {
				if (isset($value['price'])) {
					$template->total += $value['price'];
				}
			}
		} catch (PriceInvalidException $e) {}
		$this->session->total = $template->total;

		$template->render();
	}
}
