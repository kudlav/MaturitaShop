<?php
declare(strict_types=1);


namespace App\Model;

use Nette;
use Nette\Database\Table\IRow;
use Nette\Security\Identity;
use Nette\Security\Passwords;


/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator
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


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Performs an authentication.
	 * @param array $credentials
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials): Identity
	{
		list($username, $password) = $credentials;

		$row = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_EMAIL, $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('Nesprávné přihlašovací údaje.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row->heslo)) {
			throw new Nette\Security\AuthenticationException('Nesprávné přihlašovací údaje.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row->heslo)) {
			$row->update(array(
				self::COLUMN_PASSWORD => Passwords::hash($password),
			));
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD]);
		return new Identity($row->zakaznicke_cislo, 'customer', $arr);
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
