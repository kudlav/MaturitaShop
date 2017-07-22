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
		COLUMN_SHOW = 'show',
		COLUMN_PHOTO = 'photo',

		TABLE_CAT = 'categories',
		COLUMN_PARENT = 'parent';


	/** @var Nette\Database\Context */
	private $database;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}

	/**
	 * Return all product at database.
	 * @param $category Only products belonging to this cat. Don't filter when NULL.
	 * @return static
	 */
	public function getProducts($category = NULL) {

		$products = $this->database->table(self::TABLE_NAME)
			->order(self::COLUMN_TIMESTAMP.' DESC')
			->where(self::COLUMN_SHOW, 1);
		if ($category !== NULL) {
			$categories = [(int) $category];
			$allCat = $this->database->table(self::TABLE_CAT)->fetchPairs(self::COLUMN_ID, self::COLUMN_PARENT);

			foreach ($allCat as $catID => $catParent) {
				if ($catID == $category) {
					continue;
				}
				while($catParent !== NULL) {
					if (in_array($catParent, $categories)) {
						$categories[] = $catID;
						break;
					} else {
						$catParent = $allCat[$catParent];
					}
				}
			}

			$products->where(self::COLUMN_CATEGORY, $categories);
		}

		$ret = [];
		foreach ($products as $product) {
			$ret[]= [
				'id' => $product->id,
				'name' => $product->name,
				'condition' => $product->condition,
				'price' => $product->price,
				'photo' => $product->photo,
				'category' => $product->category,
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
		try {
			$query = $this->database->query("
				SELECT *
				FROM `products`
				WHERE (MATCH(`name`,`description`) AGAINST (? IN BOOLEAN MODE))
				ORDER BY 5 * MATCH(`name`) AGAINST (?) + MATCH(`description`) AGAINST (?) DESC
				", $string, $string, $string);
		}
		catch(Nette\Database\DriverException $e){
			\Tracy\Debugger::log('Unable to use fulltext search');
			$query = $this->database->query("
			SELECT *
			FROM `products`
			WHERE ".self::COLUMN_NAME." like ? OR ".self::COLUMN_DESCRIPTION." like ?", "%".$string."%", "%".$string."%");
		}


		$ret = [];
		foreach ($query as $row) {
			$ret[]= [
				'id' => $row->id,
				'name' => $row->name,
				'condition' => $row->condition,
				'price' => $row->price,
				'photo' => $row->photo,
				'category' => $row->category,
			];
		}
		return $ret;
	}

	public function getPhotos($id){
		$product = $this->getItem($id);

		if (!$product && !$products->photo) {
			return NULL;
		}

		$photoList = explode(';', $product->photo);
		return $photoList;
	}

	/**
	 * Return category with equal ID.
	 * @param $id
	 * @return Nette\Database\Table\IRow
	 */
	public function getCategory($id){
		if (!is_numeric($id)) {
			return NULL;
		}
		$category = $this->database->table(self::TABLE_CAT)->get($id);
		
		if (!$category) {
			return NULL;
		}
		return $category;
	}

	/**
	 * Return array with strings of parent categories.
	 * @param $id
	 * @return array
	 */
	public function getCategoryTree($id, $baseUrl){
		$category = $this->getCategory($id);

		if (!$category) {
			return array();
		}

		$tree = ['<a href="'.$baseUrl.'/?cat='.$category->id.'">'.$category->name.'</a>'];
		while($category->parent !== NULL) {
			$category = $category->ref(self::TABLE_CAT, self::COLUMN_PARENT);
			$tree[] = '<a href="'.$baseUrl.'/?cat='.$category->id.'">'.$category->name.'</a>';
		}

		return array_reverse($tree);
	}
}
