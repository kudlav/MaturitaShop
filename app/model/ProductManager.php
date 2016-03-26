<?php

namespace App\Model;

use Nette;


/**
 * User registrator
 */
class ProductManager extends Nette\Object
{
	const
		TABLE_NAME = 'products',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'name',
		COLUMN_DESCRIPTION = 'description',
		COLUMN_CONDITION = 'condition',
		COLUMN_PRICE = 'price',
		COLUMN_QUANTITY = 'quantiti',
		COLUMN_TIMESTAMP = 'timestamp',
		COLUMN_CATEGORY  = 'category';

	/** @var Nette\Database\Context */
	private $database;


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	public function getProducts() {
		$products = $this->database->table(self::TABLE_NAME)
			->order(self::COLUMN_TIMESTAMP.' DESC')
			->limit(8);
		return $products;
	}

	public function getItem($id) {
		$item = $this->database->table(self::TABLE_NAME)->get($id);
		if (!$item) {
			return NULL;
		}
		return $item;
	}
}
