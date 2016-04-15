<?php

namespace App\FrontModule\Presenters;

use Nette;
use Nette\Application\UI\Control;


class Order extends Control
{
	/**
	 * @var array $order
	 * @var array $products
	 */
	private $order, $products;


	public function __construct(array $order, array  $products)
	{
		parent::__construct();

		$this->order = $order;
		$this->products = $products;
	}

	public function render()
	{
		$template = $this->template;
		$template->order = $this->order;
		$template->products = $this->products;
		$template->setFile(__DIR__ . '/order.latte');

		$template->render();
	}
}
