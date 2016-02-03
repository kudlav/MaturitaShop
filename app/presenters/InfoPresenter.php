<?php

namespace App\Presenters;

use Nette;
use App\Model;


class InfoPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->anyVariable = 'any value';
	}

}
