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
		TABLE_PRODUCT = 'produkt',
		COLUMN_ID = 'katalogove_cislo',
		COLUMN_NAME = 'nazev',
		COLUMN_DESCRIPTION = 'popis',
		COLUMN_PRICE = 'cena',
		COLUMN_QUANTITY = 'mnozstvi_skladem',
		COLUMN_PHOTO = 'fotografie',
		COLUMN_CATEGORY  = 'kategorie',
		COLUMN_SHOW = 'zobrazovat',

		TABLE_RATE = 'ohodnotil',
		RATE_PRODUCT = 'katalogove_cislo',
		RATE_STARS = 'pocet_hvezdicek',
		RATE_PROS = 'klady',
		RATE_CONS = 'zapory',
		RATE_SUMMARY = 'shrnuti'
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
		$products = $this->database->table(self::TABLE_PRODUCT)
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
		$item = $this->database->table(self::TABLE_PRODUCT)->get($id);
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
			FROM `".self::TABLE_PRODUCT."`
			WHERE ".self::COLUMN_ID." like ? OR ".self::COLUMN_NAME." like ? OR ".self::COLUMN_DESCRIPTION." like ?
		", "%".$string."%", "%".$string."%", "%".$string."%");
	}

	public function getItemRating(string $id): int
	{
		$query = $this->database->query('
			SELECT AVG(pocet_hvezdicek) AS rating
			FROM ohodnotil
			WHERE katalogove_cislo = ?
			GROUP BY zakaznicke_cislo
		', $id)->fetch();

		if ($query == false) {
			return 0;
		}

		return (int) round((float)$query->rating);
	}

	public function getItemReviews(string $id): ResultSet
	{
		return $this->database->query('
			SELECT ohodnotil.*, zakaznik.jmeno, zakaznik.prijmeni
			FROM ohodnotil LEFT JOIN zakaznik ON ohodnotil.zakaznicke_cislo = zakaznik.zakaznicke_cislo
			WHERE ohodnotil.katalogove_cislo = ?
		', $id);
	}
}
