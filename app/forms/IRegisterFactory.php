<?php

namespace App\Forms;

use Nette;


interface IUserDataFactory
{
	/** @return UserDataFormFactory */
	function create();
}
