<?php

namespace App\AdminModule\Presenters;

use Nette;


class HomepagePresenter extends BasePresenter
{

	public function renderDefault() {
		$this->forward('Orders:');
	}
}
