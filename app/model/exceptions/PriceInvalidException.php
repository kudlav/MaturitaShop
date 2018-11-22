<?php
declare(strict_types=1);

namespace App\Model;


class PriceInvalidException extends \Exception
{
	/**
	 * @var string $message
	 */
	protected $message;

	/**
	 * PriceInvalidException constructor.
	 */
	public function __construct()
	{
		$this->message = 'S cenou produktu nelze počítat - obsahuje i text, nebo není zadána.';
	}
}
