<?php
declare(strict_types=1);

namespace App\FrontModule\Forms;

use Nette;


interface IUserDataFactory
{
	/**
	 * @return RegisterFormFactory
	 */
	function create(): RegisterFormFactory;
}
