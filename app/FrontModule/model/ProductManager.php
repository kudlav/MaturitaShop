<?php

namespace App\FrontModule\Model;

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
		COLUMN_CATEGORY  = 'category',
		COLUMN_SHOW = 'show';


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Return all product at database.
	 * @return static
	 */
	public function getProducts() {
		$products = $this->database->table(self::TABLE_NAME)
			->order(self::COLUMN_TIMESTAMP.' DESC')
			->where(self::COLUMN_SHOW.' = 1');

		$ret = [];
		foreach ($products as $product) {
			$ret[]= [
				'id' => $product->id,
				'name' => $product->name,
				'condition' => $product->condition,
				'price' => $product->price,
			];
		}
		return $ret;
	}

	/**
	 * Return product with equal ID.
	 * @param $id
	 * @return Nette\Database\Table\IRow
	 */
	public function getItem($id) {
		$item = $this->database->table(self::TABLE_NAME)->get($id);
		if (!$item) {
			return NULL;
		}
		return $item;
	}

	/**
	 * Return list of fulltext searched products.
	 * @param $string
	 * @return array
	 */
	public function searchProduct($string)
	{
		$query = $this->database->query("
			SELECT *
			FROM `products`
			WHERE (MATCH(`name`,`description`) AGAINST (? IN BOOLEAN MODE))
			ORDER BY 5 * MATCH(`name`) AGAINST (?) + MATCH(`description`) AGAINST (?) DESC
			", $string, $string, $string);

		$ret = [];
		foreach ($query as $row) {
			$ret[]= [
				'id' => $row->id,
				'name' => $row->name,
				'condition' => $row->condition,
				'price' => $row->price,
			];
		}
		return $ret;
	}
}
