<?php

namespace App\FrontModule\Model;

use Nette;
use Nette\Security\Passwords;


/**
 * User registrator
 */
class Registrator extends Nette\Object
{
	const
		TABLE_NAME = 'users',
		COLUMN_EMAIL = 'username',
		COLUMN_PASSWORD = 'password',
		COLUMN_ROLE = 'role',
		COLUMN_NAME = 'name',
		COLUMN_SURNAME = 'surname',
		COLUMN_STREET = 'street',
		COLUMN_CITY = 'city',
		COLUMN_POSTCODE = 'postcode',
		ROLE = 'customer';

	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Adds new user.
	 * @param Nette\Utils\ArrayHash $values
	 * @throws DuplicateNameException
	 */
	public function add($values)
	{
		$email = $values->email;
		$password = $values->password;
		$name = $values->name;
		$surname = $values->surname;
		$street = $values->street;
		$city = $values->city;
		$postcode = preg_replace('%\s+%', '', $values->postcode);

		try {
			$this->database->table(self::TABLE_NAME)->insert(array(
				self::COLUMN_EMAIL => $email,
				self::COLUMN_PASSWORD => Passwords::hash($password),
				self::COLUMN_ROLE => self::ROLE,
				self::COLUMN_NAME => $name,
				self::COLUMN_SURNAME => $surname,
				self::COLUMN_STREET => $street,
				self::COLUMN_CITY => $city,
				self::COLUMN_POSTCODE => $postcode,
			));
		} catch (Nette\Database\UniqueConstraintViolationException $e) {
			throw new DuplicateNameException;
		}
	}
}
