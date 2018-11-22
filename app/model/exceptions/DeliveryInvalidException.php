<?php
declare(strict_types=1);

namespace App\Model;


class DeliveryInvalidException extends \Exception
{
	/**
	 * @var string $message
	 */
	protected $message;

	/**
	 * DeliveryInvalidException constructor.
	 */
	public function __construct()
	{
		$this->message = 'Zvolený způsob dopravy již není platný, zvolte jiný.';
	}
}
