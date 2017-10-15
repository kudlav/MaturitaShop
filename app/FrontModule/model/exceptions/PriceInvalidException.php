<?php

namespace App\FrontModule\Model;


class PriceInvalidException extends \Exception
{
	/** @var string */
	protected $message;

	/**
	 * PriceInvalidException constructor.
	 */
	public function __construct()
	{
		$this->message = 'S cenou produktu nelze počítat - obsahuje i text, nebo není zadána.';
	}
}
