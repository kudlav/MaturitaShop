<?php
declare(strict_types=1);


namespace App\Model;

use Nette;


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

}
