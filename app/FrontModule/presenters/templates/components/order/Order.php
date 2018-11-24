<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette;
use Nette\Application\UI\Control;


class Order extends Control
{
	/**
	 * @var array $order
	 * @var array $products
	 * @var int $total
	 */
	private $order, $products, $total;

	public function __construct(array $order, array $products, int $total)
	{
		parent::__construct();

		$this->order = $order;
		$this->products = $products;
		$this->total = $total;
	}

	public function render(): void
	{
		$template = $this->template;
		$template->order = $this->order;
		$template->products = $this->products;
		$template->total = $this->total;
		$template->setFile(__DIR__ . '/order.latte');

		$template->render();
	}
}
