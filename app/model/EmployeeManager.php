<?php
declare(strict_types=1);


namespace App\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Security\Passwords;


class EmployeeManager
{
	use Nette\SmartObject;

	const
		TABLE_NAME = 'zamestnanec',
		COLUMN_ID = 'uzivatelske_jmeno',
		COLUMN_FIRSTNAME = 'jmeno',
		COLUMN_SURNAME = 'prijmeni',
		COLUMN_PASSWORD = 'heslo',
		COLUMN_ROLE = 'role'
	;

	/**
	 * @var Nette\Database\Context $database
	 */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function getEmployees(): Selection
	{
		return $this->database->table(self::TABLE_NAME);
	}

	public function disableEmployee(string $id): bool
	{
		$result = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
			self::COLUMN_PASSWORD => null,
		]);
		return ($result == 1);
	}

	public function deleteEmployee(string $id): bool
	{
		$result = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();
		return ($result == 1);
	}

	public function getEmployee(string $id): ?ActiveRow
	{
		$row = $this->database->table(self::TABLE_NAME)->get($id);
		if ($row == false) {
			return null;
		}
		return $row;
	}

	public function addEmployee(array $values): bool
	{
		$result = $this->database->table(self::TABLE_NAME)->insert([
			self::COLUMN_ID => $values['login'],
			self::COLUMN_FIRSTNAME => $values['firstname'],
			self::COLUMN_SURNAME => $values['surname'],
			self::COLUMN_PASSWORD => Passwords::hash($values['password']),
			self::COLUMN_ROLE => $values['role'],
		]);
		return ($result != false);
	}

	public function updateEmployee(string $id, $values): bool
	{
		$row = $this->database->table(self::TABLE_NAME)->get($id);
		if (isset($values['password'])) {
			$passwd = Passwords::hash($values['password']);
		}
		$result = $row->update([
			self::COLUMN_FIRSTNAME => $values['firstname'],
			self::COLUMN_SURNAME => $values['surname'],
			self::COLUMN_PASSWORD => $passwd ?? $row->heslo,
			self::COLUMN_ROLE => $values['role'],
		]);
		return ($result != false);
	}
}
