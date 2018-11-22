<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;


class HomepagePresenter extends BasePresenter
{

	/**
	 * @throws Nette\Application\AbortException
	 */
	public function renderDefault(): void
	{
		$this->forward('Orders:');
	}
}
