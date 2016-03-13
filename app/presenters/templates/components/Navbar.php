<?php

namespace App\Presenters;

use Nette\Application\UI\Control;


class Navbar extends Control
{
	private $items;
	private $title;

    public function  __construct($title, $items)
	{
		$this->items = $items;
		$this->title = $title;
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
