<?php
declare(strict_types=1);

namespace App\Model;

use Nette;


/**
 * Email validator
 */
class EmailValidator
{
	use Nette\SmartObject;

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * If email is already registered, return true
	 * @param string $email
	 * @return bool
	 */
	public function validate(string $email): bool
	{
		$row = $this->database->table(UserManager::TABLE_NAME)->where(UserManager::COLUMN_EMAIL, $email)->fetch();

		if ($row) {
			return true;
		}
		return false;
	}
}
