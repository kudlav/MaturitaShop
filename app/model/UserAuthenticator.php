<?php
declare(strict_types=1);


namespace App\Model;

use Nette;
use Nette\Security\Identity;
use Nette\Security\Passwords;


class UserAuthenticator implements Nette\Security\IAuthenticator
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
	 * Performs an authentication.
	 * @param array $credentials
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials): Identity
	{
		list($username, $password) = $credentials;

		$row = $this->database->table(UserManager::TABLE_NAME)->where(UserManager::COLUMN_EMAIL, $username)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('Nesprávné přihlašovací údaje.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row->heslo)) {
			throw new Nette\Security\AuthenticationException('Nesprávné přihlašovací údaje.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row->heslo)) {
			$row->update([
				UserManager::COLUMN_PASSWORD => Passwords::hash($password),
			]);
		}

		$arr = $row->toArray();
		unset($arr[UserManager::COLUMN_PASSWORD]);
		return new Identity($row->zakaznicke_cislo, 'customer', $arr);
	}
}
