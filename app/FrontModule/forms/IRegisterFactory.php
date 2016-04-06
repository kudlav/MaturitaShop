<?php

namespace App\FrontModule\Forms;

use Nette;


interface IUserDataFactory
{
	/** @return RegisterFormFactory */
	function create();
}
