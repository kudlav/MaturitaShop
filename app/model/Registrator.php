<?php
declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\Passwords;
use Nette\Utils\ArrayHash;


/**
 * User registrator
 */
class Registrator
{
	use Nette\SmartObject;

	/**
	 * @var Nette\Database\Context $database
	 */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Adds new user.
	 * @param ArrayHash $values
	 * @throws DuplicateNameException
	 */
	public function add(ArrayHash $values): void
	{
		try {
			$this->database->table(UserManager::TABLE_NAME)->insert(array(
				UserManager::COLUMN_FIRSTNAME => $values->name,
				UserManager::COLUMN_SURNAME => $values->surname,
				UserManager::COLUMN_EMAIL => $values->email,
				UserManager::COLUMN_PASSWORD => Passwords::hash($values->password),
			));
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}
}
