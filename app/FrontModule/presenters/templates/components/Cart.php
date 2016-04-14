<?php

namespace App\FrontModule\Presenters;

use Nette;
use Nette\Application\UI\Control;
use App\FrontModule\Model\CartManager;
use Nette\Security\User;


class Cart extends Control
{

	/**
	 * @var User $user
	 * @var CartManager $cartManager
	 */
	private $user, $cartManager;


	public function __construct(User $user, CartManager $cartManager)
	{
		parent::__construct();

		$this->user = $user;
		$this->cartManager = $cartManager;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/cart.latte');
		$template->cart = $this->getCounts();

		$template->render();
	}

	private function getCounts() {
		if ($this->user->isLoggedIn()) {
			$ret = [
				'count' => $this->cartManager->getCount($this->user->getId()),
				'price' => $this->cartManager->getPrice($this->user->getId()),
			];
		} else {
			$ret = [
				'count' => 0,
				'price' => 0,
			];
		}
		return $ret;
	}
}
