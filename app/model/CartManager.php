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

	public function getCount($id)
	{
		$query = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id);

		$ret = 0;
		foreach ($query as $row) {
			$ret+= $row->quantity;
		}
		return $ret;
	}

	public function getPrice($id)
	{
		$query = $this->database->table(self::TABLE_NAME)->where(self::COLUMN_ID, $id);

		$ret = 0;
		foreach ($query as $row) {
			$ret+= $row->quantity * $row->products->price;
		}
		return $ret;
	}
}
