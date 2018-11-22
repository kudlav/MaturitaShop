<?php
declare(strict_types=1);

namespace App\Model;


class PaymentInvalidException extends \Exception
{
	/**
	 * @var string $message
	 */
	protected $message;

	/**
	 * PaymentInvalidException constructor.
	 */
	public function __construct()
	{
		$this->message = 'Zvolený způsob platby již není platný, zvolte jiný.';
	}
}
