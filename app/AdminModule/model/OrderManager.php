<?php

namespace App\AdminModule\Model;

use Nette;


/**
 * User registrator
 */
class OrderManager extends Nette\Object
{
	const
		TABLE_ORDERS = 'orders',
		COLUMN_ID = 'id',
		COLUMN_CUSTOMER = 'customer',
		COLUMN_TIMESTAMP = 'timestamp',
		COLUMN_TOTAL = 'total',
		COLUMN_STATE = 'state',
		COLUMN_NOTE = 'note',

		TABLE_ORDERED = 'ordered_products',
		COLUMN_ORDERS_ID = 'orders_id';


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	/**
	 * Return array of orders in progress.
	 * @return array
	 */
	public function getOrdersInProgress()
	{
		$query = $this->database->table(self::TABLE_ORDERS)
			->where(self::COLUMN_STATE.' NOT LIKE ?', 'objednávka vyřízena')
			->order(self::COLUMN_TIMESTAMP.' DESC');

		$orders = [];
		foreach ($query as $row) {
			$orders[] = [
				'id' => $row->id,
				'timestamp' => $row->timestamp,
				'customer' => [$row->ref('users', 'customer')->name, $row->ref('users', 'customer')->surname],
				'total' => $row->total,
				'delivery' => $row->ref('delivery', 'delivery')->name,
				'payment' => $row->ref('payment', 'payment')->name,
				'state' => $row->state,
				'note' => $row->note,
			];
		}

		return $orders;
	}

	public function getOrder($orderId)
	{
		$row = $this->database->table(self::TABLE_ORDERS)->get($orderId);

		$orders = [
			'id' => $row->id,
			'timestamp' => $row->timestamp,
			'customer' => [$row->ref('users','customer')->name, $row->ref('users','customer')->surname],
			'total' => $row->total,
			'delivery' => 'todoD',
			'payment' => 'totoP',
			'state' => $row->state,
			'note' => $row->note,
		];

		return $orders;
	}

	public function deleteOrder($orderId) {
		$this->database->beginTransaction();
		$ordered = $this->database->table(self::TABLE_ORDERED)->where(self::COLUMN_ORDERS_ID.' = ?',$orderId)->delete();
		$orders = $this->database->table(self::TABLE_ORDERS)->get($orderId)->delete();
		$this->database->commit();
		if ($ordered && $orders) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
