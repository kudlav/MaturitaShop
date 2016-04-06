<?php

namespace App\FrontModule\Model;

use Nette;


/**
 * Email validator
 */
class EmailValidator extends Nette\Object
{
	const
		TABLE_NAME = 'users',
		COLUMN_USERNAME = 'username';

	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * If email is already registered, return true
	 * @param $email
	 * @return bool
	 */
	public function validate($email)
	{
		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_USERNAME, $email)->fetch();

		if ($row) {
			return true;
		}
		return false;
	}
}
