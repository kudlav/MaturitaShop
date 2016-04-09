<?php

namespace App\FrontModule\Model;


class DisabledItemException extends \Exception
{
	/** @var string */
	protected $message;

	/**
	 * DisabledItemException constructor.
	 * @param string $message
	 */
	public function __construct($message)
	{
		$this->message = 'Produkt '.sprintf("%05d",$message['id']).' ('.$message['name'].') již nelze objednat, odeberte jej.';
	}
}
