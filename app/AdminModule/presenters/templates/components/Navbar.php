<?php

namespace App\AdminModule\Presenters;

use Nette\Application\UI\Control;


class Navbar extends Control
{
	/**
	 * @var string $heading
	 * @var array $items
	 */
	private $heading;
	private $items;

	public function  __construct($heading, $items)
	{
		parent::__construct();
		$this->heading = $heading;
		$this->items = $items;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/navbar.latte');
		$template->items = $this->items;
		$template->heading = $this->heading;
		$template->page = $this->getPresenter()->getName().':'.$this->getPresenter()->getAction();

		$template->render();
	}
}
