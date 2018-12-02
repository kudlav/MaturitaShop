<?php
declare(strict_types=1);

namespace App\Model;

use mysql_xdevapi\Exception;
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
        COLUMN_SUPPLIER = 'dodavatel',

		TABLE_RATE = 'ohodnotil',
		RATE_PRODUCT = 'katalogove_cislo',
		RATE_USER = 'zakaznicke_cislo',
		RATE_STARS = 'pocet_hvezdicek',
		RATE_PROS = 'klady',
		RATE_CONS = 'zapory',
		RATE_SUMMARY = 'shrnuti',

        TABLE_EXTENDS = 'upresnuje',
        EXTENDS_NAME = 'nazev',
        EXTENDS_VALUE = 'hodnota'
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
     * @param int $show Show all products except hidden ones.
	 * @return Selection
	 */
	public function getProducts(string $category = "", int $show = 1): Selection {

	    if ($show){
            $products = $this->database->table(self::TABLE_PRODUCT)
                ->where(self::COLUMN_SHOW, 1)
                ->order(self::COLUMN_ID.' DESC')
            ;
        } else {
            $products = $this->database->table(self::TABLE_PRODUCT)
                ->order(self::COLUMN_ID.' DESC')
            ;
        }


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
			GROUP BY katalogove_cislo
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

	public function getItemReviewByUser(string $productId, int $userId): ResultSet
	{
		return $this->database->query('
			SELECT ohodnotil.*, zakaznik.jmeno, zakaznik.prijmeni
			FROM ohodnotil LEFT JOIN zakaznik ON ohodnotil.zakaznicke_cislo = zakaznik.zakaznicke_cislo
			WHERE ohodnotil.katalogove_cislo = ? AND ohodnotil.zakaznicke_cislo = ?
		', $productId, $userId);
	}

	public function setItemReviewByUser(string $productId, int $userId, int $rate, ?string $pros=null, ?string $cons=null, ?string $summary=null): bool
	{
		 $this->database->table(self::TABLE_RATE)
		 	->where(self::RATE_PRODUCT, $productId)
		 	->where(self::RATE_USER, $userId)
		 	->delete()
		 ;

		 $result = $this->database->table(self::TABLE_RATE)->insert([
		 	self::RATE_PRODUCT => $productId,
			self::RATE_USER => $userId,
			self::RATE_STARS => $rate,
		 	self::RATE_PROS => $pros,
		 	self::RATE_CONS => $cons,
		 	self::RATE_SUMMARY => $summary,
		 ]);

		 return ($result != false);
	}

    /**
     * Delete product
     * @param string $product Product to be deleted
     * @return bool Returns true if product was deleted
     */
	public function deleteItem(string $product): bool
    {
        try{
            $this->database->beginTransaction();
            $extend = $this->database->table(self::TABLE_EXTENDS)->where(self::COLUMN_ID, $product)->delete();
            $product_deleted = $this->database->table(self::TABLE_PRODUCT)->where(self::COLUMN_ID, $product)->delete();
            $this->database->commit();
        } catch (Nette\Database\ForeignKeyConstraintViolationException $e) {
            return false;
        }

        return ($product_deleted == 1);
    }

    /**
     * Create new product
     * @param array $values Information about product
     * @return bool True if product was created
     */
    public function addProduct(array $values): bool
    {
        $result = $this->database->table(self::TABLE_PRODUCT)->insert([
            self::COLUMN_ID => $values['id'],
            self::COLUMN_NAME => $values['name'],
            self::COLUMN_DESCRIPTION => $values['description'],
            self::COLUMN_PRICE => $values['price'],
            self::COLUMN_QUANTITY => $values['stock'],
            self::COLUMN_SHOW => $values['show'],
            self::COLUMN_CATEGORY => $values['category']
        ]);
        return ($result != false);
    }

    /**
     * Update existing product
     * @param string $id Catalog number of updated product
     * @param array $values Information about product
     * @return bool True if product was updated
     */
    public function updateProduct(string $id, array $values): bool
    {
        $row = $this->database->table(self::TABLE_PRODUCT)->get($id);

        $result = $row->update([
            self::COLUMN_NAME => $values['name'],
            self::COLUMN_DESCRIPTION => $values['description'],
            self::COLUMN_PRICE => $values['price'],
            self::COLUMN_QUANTITY => $values['stock'],
            self::COLUMN_SHOW => $values['show'],
            self::COLUMN_CATEGORY => $values['category']
        ]);
        return ($result != false);
    }
}
