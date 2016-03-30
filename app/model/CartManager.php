<?php

namespace App\Model;

use Nette;


/**
 * Cart management.
 */
class CartManager extends Nette\Object
{
	const
		TABLE_NAME = 'baskets',
		COLUMN_ID = 'users_id';


	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Return number of items in cart of user.
	 * @param int $id
	 * @return int
	 */
	public function getCount($id)
	{
		$query = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id);

		$ret = 0;
		foreach ($query as $row) {
			$ret+= $row->quantity;
		}
		return $ret;
	}

	/**
	 * Return total price of cart of user.
	 * @param int $id
	 * @return int
	 */
	public function getPrice($id)
	{
		$query = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id);

		$ret = 0;
		foreach ($query as $row) {
			$ret+= $row->quantity * $row->products->price;
		}
		return $ret;
	}

	/**
	 * Return array of items in cart of user.
	 * @param int $id
	 * @return array
	 */
	public function getItems($id)
	{
		$query = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id);

		$ret = array();
		foreach ($query as $row) {
			$ret[]= [
				'id' => $row->products_id,
				'count' => $row->quantity,
				'name' => $row->products->name,
				'quantity' => $row->products->quantity,
				'price' => $row->products->price,
			];
		}
		return $ret;
	}
}
