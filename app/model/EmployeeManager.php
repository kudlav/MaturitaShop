<?php
declare(strict_types=1);


namespace App\Model;

use Nette;


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

}
