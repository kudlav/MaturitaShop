<?php

namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
	protected function createComponentNavbar()
	{
		$items = [
			'Citroen' => ['Homepage:'],
			'Peugeot' => ['Homepage:'],
			'Fiat' => ['Homepage:'],
		];
		$control = new Navbar('Kategorie', $items);
		return $control;
	}
}
