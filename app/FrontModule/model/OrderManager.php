<?php

namespace App\FrontModule\Model;

use Nette;
use Nette\Http\SessionSection;


/**
 * Order manager
 */
class OrderManager extends Nette\Object
{
	const
		STATE = 'čeká na vyřízení',

		TABLE_ORDERS = 'orders',
		COLUMN_ID = 'id',
		COLUMN_CUSTOMER = 'customer',
		COLUMN_DELIVERY = 'delivery',
		COLUMN_PAYMENT = 'payment',
		COLUMN_TIMESTAMP = 'timestamp',
		COLUMN_TOTAL = 'total',
		COLUMN_STATE = 'state',
		COLUMN_NOTE = 'note',

		TABLE_ORDERED = 'ordered_products',
		COLUMN_ORDER_ID = 'orders_id',
		COLUMN_PRODUCT = 'products_id',
		COLUMN_PRICE = 'price',
		COLUMN_QUANTITY = 'quantity',

		TABLE_BASKETS = 'baskets',
		COLUMN_USER = 'users_id',

		TABLE_DELIVERY = 'delivery',
		TABLE_PAYMENT = 'payment',
		COLUMN_SHOW = 'show',

		VIEW_ENUM = 'enum',
		ENUM_TABLE = 'TABLE',
		ENUM_COLUMN = 'COLUMN',
		ENUM_ENUM = 'ENUM';


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
	public function detectPurchasePhase($session)
	{
		if (isset($session->delivery, $session->payment, $session->note) AND !isset($session->back)) {
			return 1;
		}
		return 0;
	}

	/**
	 * Return array of activated delivery methods.
	 * @param bool $full Brief or Full description of methods.
	 * @return array
	 */
	public function getDelivery($full = FALSE)
	{
		$query = $this->database->table(self::TABLE_DELIVERY)
			->where(self::COLUMN_SHOW.' = 1');

		if ($full) {
			return $this->deliveryPaymentFull($query);
		} else {
			return $this->deliveryPaymentBrief($query);
		}
	}

	/**
	 * Return array of activated payment methods.
	 * @param bool $full Brief or Full description of methods.
	 * @return array
	 */
	public function getPayment($full = FALSE)
	{
		$query = $this->database->table(self::TABLE_PAYMENT)
			->where(self::COLUMN_SHOW.' = 1');

		if ($full) {
			return $this->deliveryPaymentFull($query);
		} else {
			return $this->deliveryPaymentBrief($query);
		}
	}

	private function deliveryPaymentBrief($query) {
		$ret = [];
		foreach ($query as $row) {
			$ret[$row->id] = $row->name.' ('.$row->price.' Kč)';
		}
		return $ret;
	}

	private function deliveryPaymentFull($query) {
		$ret = [];
		foreach ($query as $row) {
			$ret[$row->id] = [
				'name' => $row->name,
				'price' => $row->price,
				'tooltip' => $row->tooltip,
				'type' => $row->type,
			];
		}
		return $ret;
	}

	/**
	 * Return array of user's orders.
	 * @param $userId
	 * @return array
	 */
	public function getUserOrders($userId)
	{
		$query = $this->database->table(self::TABLE_ORDERS)
			->where(self::COLUMN_CUSTOMER, $userId)
			->order(self::COLUMN_TIMESTAMP.' DESC');

		$ret = [];
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
	 * @throws DeliveryInvalidException
	 * @throws DisabledItemException
	 * @throws DuplicateNameException
	 * @throws PaymentInvalidException
	 */
	public function orderProducts(array $items, SessionSection $session, int $userId)
	{
		if (!array_key_exists($session->delivery, $this->getDelivery())) {
			unset($session->delivery);
			unset($session->payment);
			throw new DeliveryInvalidException;
		}
		if (!array_key_exists($session->payment, $this->getPayment())) {
			unset($session->delivery);
			unset($session->payment);
			throw new PaymentInvalidException;
		}

		$this->database->beginTransaction();
		$orders = $this->database->table(self::TABLE_ORDERS)->insert(array(
			self::COLUMN_CUSTOMER => $userId,
			self::COLUMN_DELIVERY => $session->delivery,
			self::COLUMN_PAYMENT => $session->payment,
			self::COLUMN_TIMESTAMP => date("Y-m-d H:i:s"),
			self::COLUMN_TOTAL => $session->total,
			self::COLUMN_STATE => self::STATE,
			self::COLUMN_NOTE => $session->note,
		));

		if ($orders) {
			try {
				foreach ($items as $product) {
					if ($product['show']) {
						$orderedProducts = $this->database->table(self::TABLE_ORDERED)->insert(array(
							self::COLUMN_ORDER_ID => $orders->id,
							self::COLUMN_PRODUCT => $product['id'],
							self::COLUMN_PRICE => $product['price'],
							self::COLUMN_QUANTITY => $product['count'],
						));

					$products = $this->database->table('products')->get($product['id']);
					$products->update(array(
						'quantity' => ($products->quantity - $product['count'])
					));
					} else {
						$this->database->rollBack();
						throw new DisabledItemException($product);
					}
					if (!$orderedProducts) {
						$this->database->rollBack();
						return FALSE;
					}
				}
			} catch (Nette\Database\UniqueConstraintViolationException $e) {
				$this->database->rollBack();
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
				'customer' => [$row->ref('users', 'customer')->name, $row->ref('users', 'customer')->surname, $row->customer],
				'total' => $row->total,
				'delivery' => $row->ref('delivery', 'delivery')->name,
				'payment' => $row->ref('payment', 'payment')->name,
				'state' => $row->state,
				'note' => $row->note,
			];
		}

		return $orders;
	}

	/**
	 * Return array with information of requested order.
	 * @param $orderId
	 * @return array
	 */
	public function getOrder($orderId)
	{
		$row = $this->database->table(self::TABLE_ORDERS)->get($orderId);

		$orders = [
			'id' => $row->id,
			'timestamp' => $row->timestamp,
			'customerName' => $row->ref('users','customer')->name,
			'customerSurname' => $row->ref('users','customer')->surname,
			'customerUsername' => $row->ref('users','customer')->username,
			'customerPhone' => $row->ref('users','customer')->phone,
			'customerStreet' => $row->ref('users','customer')->street,
			'customerCity' => $row->ref('users','customer')->city,
			'customerPostcode' => $row->ref('users','customer')->postcode,
			'total' => $row->total,
			'delivery' => $row->ref('delivery', 'delivery')->name,
			'deliveryPrice' => $row->ref('delivery', 'delivery')->price,
			'payment' => $row->ref('payment', 'payment')->name,
			'paymentPrice' => $row->ref('payment', 'payment')->price,
			'state' => $row->state,
			'note' => $row->note,
		];

		return $orders;
	}

	/**
	 * Return array of ordered products in requested order.
	 * @param $orderId
	 * @return array
	 */
	public function getOrderedProducts($orderId)
	{
		$query = $this->database->table(self::TABLE_ORDERED)
			->where(self::COLUMN_ORDER_ID.' = ?', $orderId);

		$products = [];
		foreach ($query as $row) {
			$products[] = [
				'price' => $row->price,
				'quantity' => $row->quantity,
				'id' => $row->orders_id,
				'name' => $row->products->name,
				'description' => $row->products->description,
				'condition' => $row->products->condition,
				'weight' => $row->products->weight,
			];
		}

		return $products;
	}

	/**
	 * Delete order witch equal ID.
	 * @param $orderId
	 * @return bool Return TRUE in case of success.
	 */
	public function deleteOrder($orderId)
	{
		$this->database->beginTransaction();
		$ordered = $this->database->table(self::TABLE_ORDERED)->where(self::COLUMN_ORDER_ID.' = ?',$orderId)->delete();
		$orders = $this->database->table(self::TABLE_ORDERS)->get($orderId)->delete();
		$this->database->commit();
		if ($ordered && $orders) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Return array of possible states of order.
	 * @return array
	 */
	public function getStates()
	{
		$enum = $this->database->table(self::VIEW_ENUM)
			->where('`'.self::ENUM_TABLE.'` = ? && `'.self::ENUM_COLUMN.'` = ?', self::TABLE_ORDERS, self::COLUMN_STATE)
			->fetch();

		$enum = preg_replace('#(enum\()|\)|\'#','',$enum->ENUM);
		$enum = explode(',',$enum);
		return $enum;
	}

	/**
	 * Change state of selected order.
	 * @param $orderId
	 * @param $value
	 */
	public function changeState($orderId, $value)
	{
		$row = $this->database->table(self::TABLE_ORDERS)->get($orderId)
			->update(array(
				self::COLUMN_STATE => $value
			));
	}
}
