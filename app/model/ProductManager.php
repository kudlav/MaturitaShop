<?php
declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Database\ResultSet;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;


class ProductManager
{
	use Nette\SmartObject;

	const
		TABLE_NAME = 'produkt',
		COLUMN_ID = 'katalogove_cislo',
		COLUMN_NAME = 'nazev',
		COLUMN_DESCRIPTION = 'popis',
		COLUMN_PRICE = 'cena',
		COLUMN_QUANTITY = 'mnozstvi_skladem',
		COLUMN_PHOTO = 'fotografie',
		COLUMN_CATEGORY  = 'kategorie',
		COLUMN_SHOW = 'zobrazovat'
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
	 * Return all product at database.
	 * @param string $category Only products belonging to this cat. Don't filter when NULL.
	 * @return Selection
	 */
	public function getProducts(string $category = ""): Selection {
		$products = $this->database->table(self::TABLE_NAME)
			->where(self::COLUMN_SHOW, 1)
			->order(self::COLUMN_ID.' DESC');
		if ($category != "") {
			$products->where(self::COLUMN_CATEGORY, $category);
		}

		return $products;
	}

	/**
	 * Return product with equal ID.
	 * @param string $id
	 * @return IRow
	 */
	public function getItem(string $id): ?Irow {
		$item = $this->database->table(self::TABLE_NAME)->get($id);
		if (!$item) {
			return null;
		}
		return $item;
	}

	/**
	 * Return list of fulltext searched products.
	 * @param string $string
	 * @return ResultSet
	 */
	public function searchProduct(string $string): ResultSet
	{
		return $this->database->query("
			SELECT *
			FROM `".self::TABLE_NAME."`
			WHERE ".self::COLUMN_ID." like ? OR ".self::COLUMN_NAME." like ? OR ".self::COLUMN_DESCRIPTION." like ?
		", "%".$string."%", "%".$string."%", "%".$string."%");
	}
}
