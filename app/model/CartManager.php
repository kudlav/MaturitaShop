<?php
declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Database\ResultSet;


/**
 * Cart management.
 */
class CartManager
{
	use Nette\SmartObject;

	const
		TABLE_BASKETS = 'vlozil_do_kosiku',
		COLUMN_USERS_ID = 'zakaznicke_cislo',
		COLUMN_PRODUCTS_ID = 'katalogove_cislo',
		COLUMN_QUANTITY = 'pocet_kusu'
	;

	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Return number of items in cart of user.
	 * @param int $userId
	 * @return int
	 */
	public function getCount(int $userId): int
	{
		$query = $this->database->table(self::TABLE_BASKETS)->where(self::COLUMN_USERS_ID, $userId);

		$ret = 0;
		foreach ($query as $row) {
			$ret+= $row->pocet_kusu;
		}
		return $ret;
	}

	/**
	 * Return total price of cart of specified user.
	 * @param int $userId
	 * @return int The price of user cart.
	 */
	public function getPrice(int $userId): int
	{
		$query = $this->database->table(self::TABLE_BASKETS)->where(self::COLUMN_USERS_ID, $userId);

		$ret = 0;
		foreach ($query as $row) {
			$ret+= $row->pocet_kusu * $row->ref(ProductManager::TABLE_NAME, self::COLUMN_PRODUCTS_ID)->cena;
		}
		return $ret;
	}

	/**
	 * Return array of items in cart of user.
	 * @param int $userId
	 * @return ResultSet
	 */
	public function getItems(int $userId): ResultSet
	{
		$query = $this->database->query("
			SELECT produkt.*, vlozil_do_kosiku.pocet_kusu
			FROM vlozil_do_kosiku LEFT JOIN produkt ON vlozil_do_kosiku.katalogove_cislo = produkt.katalogove_cislo
			WHERE vlozil_do_kosiku.zakaznicke_cislo = ?
		", $userId);

		return $query;
	}

	/**
	 * Remove product from user cart.
	 * @param int $userId
	 * @param string $productId
	 * @return int Return number of removed products.
	 */
	public function removeItem(int $userId, string $productId): int
	{
		$state = $this->database->table(self::TABLE_BASKETS)
			->where(self::COLUMN_USERS_ID." = ? AND ".self::COLUMN_PRODUCTS_ID." = ?", $userId, $productId)
			->delete();
		return $state;
	}

	/**
	 * Add product from database to user basket or increase quantity.
	 * @param int $userId
	 * @param string $productId
	 * @param int $quantity Zero means +1
	 * @return bool Return true on success.
	 */
	public function addItem(int $userId, string $productId, int $quantity = 0): bool
	{
		$product = $this->database->table(ProductManager::TABLE_NAME)->get($productId);
		if ($product && $product->zobrazovat) {
			$basket = $this->database->table(self::TABLE_BASKETS)
				->where(self::COLUMN_USERS_ID." = ? AND ".self::COLUMN_PRODUCTS_ID." = ?", $userId, $productId)->fetch();
			if ($basket !== false) {
				if ($quantity > 0) {
					$basket->update(array(
						'pocet_kusu' => $quantity,
					));
					return true;
				} elseif ($quantity == 0) {
					$basket->update(array(
						'pocet_kusu' => $basket->pocet_kusu + 1,
					));
					return true;
				}
			} else {
				$this->database->table(self::TABLE_BASKETS)->insert(array(
					self::COLUMN_USERS_ID => $userId,
					self::COLUMN_PRODUCTS_ID => $productId,
					self::COLUMN_QUANTITY => 1,
				));
				return true;
			}
		}
		return false;
	}
}
