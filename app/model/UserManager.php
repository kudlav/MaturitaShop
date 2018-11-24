<?php
declare(strict_types=1);


namespace App\Model;

use Nette;
use Nette\Database\Table\IRow;


class UserManager
{
	use Nette\SmartObject;

	const
		TABLE_NAME = 'zakaznik',
		COLUMN_ID = 'zakaznicke_cislo',
		COLUMN_FIRSTNAME = 'jmeno',
		COLUMN_SURNAME = 'prijmeni',
		COLUMN_EMAIL = 'email',
		COLUMN_PASSWORD = 'heslo'
	;

	/**
	 * @var Nette\Database\Context $database
	 */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Return row of requested user.
	 * @param int $userId
	 * @return IRow|null
	 */
	public function getContact(int $userId): ?IRow
	{
		$row = $this->database->table(self::TABLE_NAME)->get($userId);

		if ($row === false) {
			return null;
		}

		return $row;
	}
}
