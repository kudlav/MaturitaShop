<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette\Application\UI\Control;


class Navbar extends Control
{
	/**
	 * @var string $heading
	 * @var array $items
	 * @var string $paramName
	 */
	private $heading;
	private $items;
	private $paramName;

	public function  __construct(string $heading, array $items, string $paramName = 'p')
	{
		parent::__construct();

		$this->heading = $heading;
		$this->items = $items;
		$this->paramName = $paramName;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/navbar.latte');
		$template->items = $this->items;
		$template->heading = $this->heading;
		$template->paramName = $this->paramName;
		$template->page = $this->getPresenter()->getName().':'.$this->getPresenter()->getAction();

		$template->render();
	}
}
