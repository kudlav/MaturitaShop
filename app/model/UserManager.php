<?php
declare(strict_types=1);


namespace App\Model;

use Nette;
use Nette\Database\ResultSet;
use Nette\Database\Row;
use Nette\Security\Passwords;
use Nette\Utils\ArrayHash;


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
	 * @param array $values
	 * @return bool
	 */
	public function addUser(array $values): bool
	{
		$result = $this->database->table(UserManager::TABLE_NAME)->insert([
			UserManager::COLUMN_FIRSTNAME => $values['firstname'],
			UserManager::COLUMN_SURNAME => $values['surname'],
			UserManager::COLUMN_EMAIL => $values['email'],
			UserManager::COLUMN_PASSWORD => Passwords::hash($values['password']),
		]);

		return ($result == false);
	}

	/**
	 * @param int $userId
	 * @return Row|null
	 */
	public function getUser(int $userId): ?Row
	{
		$row = $this->database->query('
			SELECT zakaznik.*, objednavka.ulice, objednavka.mesto, objednavka.psc, objednavka.datum_cas
			FROM zakaznik LEFT JOIN objednavka ON zakaznik.zakaznicke_cislo = objednavka.zakaznicke_cislo
				AND objednavka.cislo_objednavky = (
					SELECT MAX(cislo_objednavky)
					FROM objednavka o
					WHERE objednavka.zakaznicke_cislo = o.zakaznicke_cislo)
			WHERE zakaznik.zakaznicke_cislo = ?
		', $userId)->fetch();

		return $row == false ? null : $row;
	}

	/**
	 * @return ResultSet
	 */
	public function getUsers(): ResultSet
	{
		return $this->database->query('
			SELECT zakaznik.*, objednavka.ulice, objednavka.mesto, objednavka.psc, objednavka.datum_cas
			FROM zakaznik LEFT JOIN objednavka ON zakaznik.zakaznicke_cislo = objednavka.zakaznicke_cislo
				AND objednavka.cislo_objednavky = (
					SELECT MAX(cislo_objednavky)
					FROM objednavka o
					WHERE objednavka.zakaznicke_cislo = o.zakaznicke_cislo)
		');
	}

	/**
	 * @param int $id
	 * @return bool
	 */
	public function deleteUser(int $id): bool
	{
		try{
			$result = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();
			return ($result == 1);
		}
		catch (Nette\Database\ForeignKeyConstraintViolationException $e) {
			return false;
		}
	}

	/**
	 * @param string $id
	 * @return bool
	 */
	public function disableUser(string $id)
	{
		$result = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->update([
			self::COLUMN_PASSWORD => null,
		]);
		return ($result == 1);
	}

	public function updateUser(string $id, array $values)
	{
		$row = $this->database->table(self::TABLE_NAME)->get($id);
		if (isset($values['password'])) {
			$passwd = Passwords::hash($values['password']);
		}
		$result = $row->update([
			self::COLUMN_FIRSTNAME => $values['firstname'],
			self::COLUMN_SURNAME => $values['surname'],
			self::COLUMN_PASSWORD => $passwd ?? $row->heslo,
			self::COLUMN_EMAIL => $values['email'],
		]);
		return ($result != false);
	}
}
