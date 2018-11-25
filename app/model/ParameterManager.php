<?php
declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Database\Table\Selection;


class ParameterManager
{
	use Nette\SmartObject;

	const
		TABLE_PARAMETER = 'parametr',
		PARAMETER_NAME = 'nazev',

		TABLE_PROPERTY = 'upresnuje',
		PROPERTY_PRODUCT = 'katalogove_cislo',
		PROPERTY_NAME = 'nazev',
		PROPERTY_VALUE = 'hodnota'
	;

	/**
	 * @var Nette\Database\Context $database
	 */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function getProductParameters(string $productId): Selection
	{
		return $this->database->table(self::TABLE_PROPERTY)->where(self::PROPERTY_PRODUCT, $productId);
	}

}
