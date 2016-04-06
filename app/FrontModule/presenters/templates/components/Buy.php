<?php

namespace App\FrontModule\Presenters;

use Nette\Application\UI\Control;
use Nette\Http\SessionSection;
use App\FrontModule\Model\CartManager;
use Nette\Security\User;


class Buy extends Control
{
	const
		DELIVERY_METHODS = [
			['Česká pošta', 99],
			['PPL', 99],
			['Uloženka', 20],
		],
		PAYMENT_METHODS = [
			['Převodem předem', 0],
			['Kartou online', 99],
			['Dobírkou při převzetí', 49],
		];

	/** @var SessionSection */
	private $session;

	/** @var User */
	private $user;

	/** @var CartManager */
	private $cartManager;


	public function  __construct(SessionSection $session, User $user, CartManager $cartManager)
	{
		$this->session = $session;
		$this->user = $user;
		$this->cartManager = $cartManager;
	}

	public function render()
	{
		$userId = $this->user->id;
		$template = $this->template;

		$template->setFile(__DIR__ . '/buy.latte');
		$template->items = $this->cartManager->getItems($userId);
		$template->form = [
			'Doručení' => self::DELIVERY_METHODS[$this->session->delivery],
			'Platba' => self::PAYMENT_METHODS[$this->session->payment],
			'Vaše poznámka' => [$this->session->note],
			];

		$template->total = $this->cartManager->getPrice($userId);
		foreach ($template->form as $item => $value) {
			if (isset($value[1])) {
				$template->total += $value[1];
			}
		}
		$this->session->total = $template->total;

		$template->render();
	}
}
