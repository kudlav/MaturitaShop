<?php

namespace App\FrontModule\Presenters;

use Nette;
use Nette\Application\UI\Control;


class Order extends Control
{
	/**
	 * @var array $order
	 * @var array $products
	 * @var bool $show_order_code
	 */
	private $order, $products, $show_order_code;


	public function __construct(array $order, array  $products, bool $show_order_code)
	{
		parent::__construct();

		$this->order = $order;
		$this->products = $products;
		$this->show_order_code = $show_order_code;
	}

	public function render()
	{
		$template = $this->template;
		$template->order = $this->order;
		$template->products = $this->products;
		$template->show_order_code = $this->show_order_code;
		$template->setFile(__DIR__ . '/order.latte');

		$template->render();
	}
}
