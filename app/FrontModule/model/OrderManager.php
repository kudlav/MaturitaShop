<?php

namespace App\FrontModule\Model;

use Nette;
use Nette\Http\SessionSection;


/**
 * User registrator
 */
class OrderManager extends Nette\Object
{
	const
		TABLE_ORDERS = 'orders',
		COLUMN_CUSTOMER = 'customer',
		COLUMN_TIMESTAMP = 'timestamp',
		COLUMN_TOTAL = 'total',
		COLUMN_STATE = 'state',
		COLUMN_NOTE = 'note',

		TABLE_ORDERED_PRODUCTS = 'ordered_products',
		COLUMN_ORDER = 'orders_id',
		COLUMN_PRODUCT = 'products_id',
		COLUMN_PRICE = 'price',
		COLUMN_QUANTITY = 'quantity',

		TABLE_BASKETS = 'baskets',
		COLUMN_USER = 'users_id';


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Check if order parameters are already saved in session.
	 * @param Nette\Http\SessionSection $session Section 'buy'.
	 * @return int
	 */
	public function detectPurchasePhase($session) {
		if (isset($session->delivery, $session->payment, $session->note) AND !isset($session->back)) {
			return 1;
		}
		return 0;
	}

	/**
	 * Return array of user's orders.
	 * @param $userId
	 * @return array
	 */
	public function getOrders($userId)
	{
		$query = $this->database->table(self::TABLE_ORDERS)
			->where(self::COLUMN_CUSTOMER, $userId)
			->order(self::COLUMN_TIMESTAMP.' DESC');

		$ret = array();
		foreach ($query as $row) {
			$ret[]= [
				'id' => $row->id,
				'timestamp' => $row->timestamp,
				'total' => $row->total,
				'state' => $row->state,
			];
		}
		return $ret;
	}

	/**
	 * Order items.
	 * @param array $items Items to order.
	 * @param SessionSection $session Contains total and note.
	 * @param int $userId User that orders items.
	 * @return bool True if OK.
	 * @throws DuplicateNameException
	 */
	public function orderProducts(array $items, SessionSection $session, int $userId)
	{
		$this->database->beginTransaction();
		$orders = $this->database->table(self::TABLE_ORDERS)->insert(array(
			self::COLUMN_CUSTOMER => $userId,
			self::COLUMN_TIMESTAMP => date("Y-m-d H:i:s"),
			self::COLUMN_TOTAL => $session->total,
			self::COLUMN_STATE => 'čeká na vyřízení',
			self::COLUMN_NOTE => $session->note,
		));

		if ($orders) {
			try {
				foreach ($items as $product) {
					$orderedProducts = $this->database->table(self::TABLE_ORDERED_PRODUCTS)->insert(array(
						self::COLUMN_ORDER => $orders->id,
						self::COLUMN_PRODUCT => $product['id'],
						self::COLUMN_PRICE => $product['price'],
						self::COLUMN_QUANTITY => $product['quantity'],
					));
					if (!$orderedProducts) {
						$this->database->rollBack();
						return FALSE;
					}
				}
			} catch (Nette\Database\UniqueConstraintViolationException $e) {
				throw new DuplicateNameException;
			}

			$basket = $query = $this->database->table(self::TABLE_BASKETS)
				->where(self::COLUMN_USER, $userId)
				->delete();

			if ($basket) {
				$this->database->commit();
				return $orders->id;
			}
		}
		$this->database->rollBack();
		return FALSE;
	}
}
