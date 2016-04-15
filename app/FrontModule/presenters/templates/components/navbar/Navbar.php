<?php

namespace App\FrontModule\Presenters;

use Nette\Application\UI\Control;


class Navbar extends Control
{
	/**
	 * @var string $title
	 * @var array $items
	 */
	private $title;
	private $items;

	public function  __construct($title, $items)
	{
		parent::__construct();

		$this->title = $title;
		$this->items = $items;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/navbar.latte');
		$template->items = $this->items;
		$template->title = $this->title;
		$template->page = $this->getPresenter()->getName().':'.$this->getPresenter()->getAction();

		$template->render();
	}
}
