<?php

namespace App\FrontModule\Presenters;

use Nette\Application\UI\Control;


class Navbar extends Control
{
	/**
	 * @var string $title
	 * @var array $items
	 * @var string $paramName
	 */
	private $title;
	private $items;
	private $paramName;

	public function  __construct($title, $items, $paramName = 'p')
	{
		parent::__construct();

		$this->title = $title;
		$this->items = $items;
		$this->paramName = $paramName;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/navbar.latte');
		$template->items = $this->items;
		$template->title = $this->title;
		$template->paramName = $this->paramName;
		$template->page = $this->getPresenter()->getName().':'.$this->getPresenter()->getAction();

		$template->render();
	}
}
