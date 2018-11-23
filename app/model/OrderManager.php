<?php
declare(strict_types=1);

namespace App\Model;

use App\FrontModule\Presenters\Cart;
use Nette;
use Nette\Database\ResultSet;
use Nette\Http\SessionSection;


/**
 * Order manager
 */
class OrderManager
{
	use Nette\SmartObject;

	const
		STATE = 'čeká na vyřízení',

		TABLE_ORDERS = 'objednavka',
		ORDERS_ID = 'cislo_objednavky',
		ORDERS_CUSTOMER = 'zakaznicke_cislo',
		ORDERS_TIMESTAMP = 'datum_cas',
		ORDERS_STATE = 'stav',
		ORDERS_PAID = 'zaplaceno',
		ORDERS_ADDR_STREET = 'ulice',
		ORDERS_ADDR_CITY = 'mesto',
		ORDERS_ADDR_ZIP = 'psc',
		ORDERS_DELIVERY = 'zpusob_doruceni',
		ORDERS_PAYMENT = 'platebni_metoda',
		ORDERS_NOTE = 'poznamka',

		TABLE_ORDERED = 'obsahuje',
		ORDERED_ORDER = 'cislo_objednavky',
		ORDERED_CUSTOMER = 'zakaznicke_cislo',
		ORDERED_PRODUCT = 'katalogove_cislo',
		ORDERED_QUANTITY = 'mnozstvi',
		ORDERED_PRICE = 'cena'
	;

	/**
	 * @var array $parameters
	 * @var Nette\Database\Context $database
	 */
	private $parameters, $database;

	public function __construct(array $parameters, Nette\Database\Context $database)
	{
		$this->parameters = $parameters;
		$this->database = $database;
	}

	/**
	 * Check if order parameters are already saved in session.
	 * @param SessionSection $session Section 'buy'.
	 * @return int
	 */
	public function detectPurchasePhase(SessionSection $session): int
	{
		if (isset($session->note, $session->street, $session->city, $session->zip) AND !isset($session->back)) {
			if (isset($session->delivery) OR !$this->getDelivery()) {
				if (isset($session->payment) OR !$this->getPayment()) {
					return 1;
				}
			}
		}
		return 0;
	}

	/**
	 * Return array of activated delivery methods.
	 * @return array
	 */
	public function getDelivery(): array
	{
		return $this->parameters['delivery'];
	}

	/**
	 * Return array of activated payment methods.
	 * @return array
	 */
	public function getPayment(): array
	{
		return $this->parameters['payment'];
	}

	/**
	 * Return users orders.
	 * @param int $userId
	 * @return ResultSet
	 */
	public function getUserOrders(int $userId): ResultSet
	{
		$query = $this->database->query('
			SELECT objednavka.*, SUM(obsahuje.mnozstvi * obsahuje.cena) AS suma
			FROM objednavka LEFT JOIN obsahuje ON objednavka.cislo_objednavky = obsahuje.cislo_objednavky AND objednavka.zakaznicke_cislo = obsahuje.zakaznicke_cislo
			WHERE objednavka.zakaznicke_cislo = ?
			GROUP BY objednavka.cislo_objednavky
			ORDER BY objednavka.cislo_objednavky DESC
		', $userId);

		return $query;
	}

	/**
	 * Order items.
	 * @param ResultSet $items Items to order.
	 * @param SessionSection $session Contains total and note.
	 * @param int $userId User that orders items.
	 * @return int Number of new order, -1 when error occurs.
	 * @throws DeliveryInvalidException
	 * @throws PaymentInvalidException
	 * @throws DisabledItemException
	 * @throws DuplicateNameException
	 */
	public function orderProducts(ResultSet $items, SessionSection $session, int $userId): int
	{
		$delivery = $this->getDelivery();
		if (!array_key_exists($session->delivery, $delivery) AND $delivery) {
			unset($session->delivery);
			unset($session->payment);
			throw new DeliveryInvalidException;
		}

		$payment = $this->getPayment();
		if (!array_key_exists($session->payment, $payment) AND $payment) {
			unset($session->delivery);
			unset($session->payment);
			throw new PaymentInvalidException;
		}

		$this->database->beginTransaction();
		$orders = $this->database->table(self::TABLE_ORDERS)->insert(array(
			self::ORDERS_CUSTOMER => $userId,
			self::ORDERS_TIMESTAMP => date("Y-m-d H:i:s"),
			self::ORDERS_STATE => self::STATE,
			self::ORDERS_PAID => 0,
			self::ORDERS_ADDR_STREET => $session->street,
			self::ORDERS_ADDR_CITY => $session->city,
			self::ORDERS_ADDR_ZIP => preg_replace('%\s%', '', $session->zip),
			self::ORDERS_DELIVERY => $session->delivery,
			self::ORDERS_PAYMENT => $session->payment,
			self::ORDERS_NOTE => $session->note,
		));

		if ($orders) {
			try {
				foreach ($items as $product) {
					if ($product->zobrazovat) {
						$orderedProducts = $this->database->table(self::TABLE_ORDERED)->insert([
							self::ORDERED_ORDER => $orders->cislo_objednavky,
							self::ORDERED_CUSTOMER => $orders->zakaznicke_cislo,
							self::ORDERED_PRODUCT => $product->katalogove_cislo,
							self::ORDERED_QUANTITY => $product->pocet_kusu,
							self::ORDERED_PRICE => $product->cena,
						]);

						$productRow = $this->database->table(ProductManager::TABLE_NAME)->get($product->katalogove_cislo);
						$productRow->update([
							ProductManager::COLUMN_QUANTITY => ($productRow->mnozstvi_skladem - $product->pocet_kusu)
						]);
					} else {
						$this->database->rollBack();
						throw new DisabledItemException($product);
					}
					if (!$orderedProducts) {
						$this->database->rollBack();
						return -1;
					}
				}
			} catch (Nette\Database\UniqueConstraintViolationException $e) {
				$this->database->rollBack();
				throw new DuplicateNameException;
			}

			$basket = $query = $this->database->table(CartManager::TABLE_BASKETS)
				->where(CartManager::COLUMN_USERS_ID, $userId)
				->delete();

			if ($basket) {
				$this->database->commit();
				return (int) $orders->cislo_objednavky;
			}
		}
		$this->database->rollBack();
		return -1;
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
				'state' => $row->state,
				'note' => $row->note,
			];
		}

		if ($row->delivery != NULL) {
			$orders['delivery'] = $row->ref('delivery', 'delivery')->name;
		}

		if ($row->payment != NULL) {
			$orders['payment'] = $row->ref('payment', 'payment')->name;
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

		if ($row == FALSE) {
			return $row;
		}

		$orders = [
			'id' => $row->id,
			'timestamp' => $row->timestamp,
			'customerId' => $row->customer,
			'customerName' => $row->ref('users','customer')->name,
			'customerSurname' => $row->ref('users','customer')->surname,
			'customerUsername' => $row->ref('users','customer')->username,
			'customerPhone' => $row->ref('users','customer')->phone,
			'customerStreet' => $row->ref('users','customer')->street,
			'customerCity' => $row->ref('users','customer')->city,
			'customerPostcode' => $row->ref('users','customer')->postcode,
			'total' => $row->total,
			'state' => $row->state,
			'note' => $row->note,
		];

		if ($row->delivery != NULL) {
			$orders['delivery'] = $row->ref('delivery', 'delivery')->name;
			$orders['deliveryPrice'] = $row->ref('delivery', 'delivery')->price;
		}

		if ($row->payment != NULL) {
			$orders['payment'] = $row->ref('payment', 'payment')->name;
			$orders['paymentPrice'] =  $row->ref('payment', 'payment')->price;
		}

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
				'price_text' => $row->price_text,
				'quantity' => $row->quantity,
				'id' => $row->products_id,
				'name' => $row->products->name,
				'description' => $row->products->description,
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
