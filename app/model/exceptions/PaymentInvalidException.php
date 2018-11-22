<?php

namespace App\Model;


class PaymentInvalidException extends \Exception
{
	/** @var string */
	protected $message;

	/**
	 * PaymentInvalidException constructor.
	 */
	public function __construct()
	{
		$this->message = 'Zvolený způsob platby již není platný, zvolte jiný.';
	}
}
