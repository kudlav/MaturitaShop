<?php
declare(strict_types=1);


namespace App\Model;

use Nette;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;


class SupplierManager
{
	use Nette\SmartObject;

	const
		TABLE_NAME = 'dodavatel',
		COLUMN_ID = 'ico',
		COLUMN_NAME = 'nazev',
        COLUMN_CONTACT_PERSON = 'kontaktni_osoba',
		COLUMN_STREET = 'ulice',
		COLUMN_CITY = 'mesto',
		COLUMN_PSC = 'psc',
        COLUMN_DELIVERY_TIME = 'dodaci_lhuta',
        COLUMN_EMAIL = 'email',
        COLUMN_PHONE_NUMBER = 'telefon'
	;

	/**
	 * @var Nette\Database\Context $database
	 */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function getSuppliers(): Selection
	{
		return $this->database->table(self::TABLE_NAME);
	}

	public function deleteSupplier(int $id): int
	{
		try {
			return $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id)->delete();
		}
		catch (Nette\Database\ForeignKeyConstraintViolationException $e) {
			return 0;
		}
	}

	public function getSupplier(int $id): ?ActiveRow
	{
		$result = $this->database->table(self::TABLE_NAME)->get($id);

		if ($result == false) {
			return null;
		}

		return $result;
	}

	public function addSupplier(array $values): bool
	{
		$result = $this->database->table(self::TABLE_NAME)->insert([
			self::COLUMN_ID => $values['id'],
			self::COLUMN_NAME => $values['name'],
			self::COLUMN_CONTACT_PERSON => $values['fullname'],
			self::COLUMN_STREET => $values['street'],
			self::COLUMN_CITY => $values['city'],
			self::COLUMN_PSC => $values['zip'],
			self::COLUMN_DELIVERY_TIME => $values['delivery'],
			self::COLUMN_EMAIL => $values['email'],
			self::COLUMN_PHONE_NUMBER => $values['phone'],
		]);

		return ($result != false);
	}

	public function updateSupplier(int $id, array $values): bool
	{
		$result = $this->database->table(self::TABLE_NAME)->get($id)->update([
			self::COLUMN_NAME => $values['name'],
			self::COLUMN_CONTACT_PERSON => $values['fullname'],
			self::COLUMN_STREET => $values['street'],
			self::COLUMN_CITY => $values['city'],
			self::COLUMN_PSC => $values['zip'],
			self::COLUMN_DELIVERY_TIME => $values['delivery'],
			self::COLUMN_EMAIL => $values['email'],
			self::COLUMN_PHONE_NUMBER => $values['phone'],
		]);

		return ($result != false);
	}
}
