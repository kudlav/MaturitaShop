<?php

namespace App\FrontModule\Forms;

use Nette;


interface IUserDataFactory
{
	/** @return UserDataFormFactory */
	function create();
}
