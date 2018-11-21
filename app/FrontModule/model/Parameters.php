<?php

namespace App\FrontModule\Model;

use Nette;


/**
 * Provides access to parameters section of config.neon file
 */
class Parameters
{
	use Nette\SmartObject;

	private $parameters;


	public function __construct($parameters)
	{
		$this->parameters = $parameters;
	}

	public function getParam()
	{
		return $this->parameters;
	}
}
