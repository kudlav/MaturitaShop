<?php

namespace App\Presenters;

use Nette\Application\UI\Control;


class Navbar extends Control
{
	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/navbar.latte');

		$template->render();
	}
}
