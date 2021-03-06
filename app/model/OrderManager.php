<?php
declare(strict_types=1);

namespace App\Model;

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
		return $this->database->query('
			SELECT objednavka.*, SUM(obsahuje.mnozstvi * obsahuje.cena) AS suma
			FROM objednavka LEFT JOIN obsahuje ON objednavka.cislo_objednavky = obsahuje.cislo_objednavky
			WHERE objednavka.zakaznicke_cislo = ?
			GROUP BY objednavka.cislo_objednavky
			ORDER BY objednavka.cislo_objednavky DESC
		', $userId);
	}

	/**
	 * @param string $state ('in progress')
	 * @return ResultSet
	 */
	public function getOrdersByState(string $state = ""): ResultSet
	{
		$where = '';
		if ($state == 'in progress') {
			$where .= 'WHERE '. self::ORDERS_STATE .' <> "objednávka vyřízena"';
		}

		return $this->database->query("
			SELECT objednavka.*, SUM(obsahuje.mnozstvi * obsahuje.cena) AS suma, zakaznik.jmeno, zakaznik.prijmeni
			FROM objednavka
			LEFT JOIN obsahuje ON objednavka.cislo_objednavky = obsahuje.cislo_objednavky
			LEFT JOIN zakaznik ON objednavka.zakaznicke_cislo = zakaznik.zakaznicke_cislo
			$where
			GROUP BY objednavka.cislo_objednavky
			ORDER BY objednavka.cislo_objednavky DESC
		");
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

						$productRow = $this->database->table(ProductManager::TABLE_PRODUCT)->get($product->katalogove_cislo);
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
	 * Return array with information of requested order.
	 * @param int $orderId
	 * @return array
	 */
	public function getOrder(int $orderId): array
	{
		$row = $this->database->table(self::TABLE_ORDERS)
			->where(self::ORDERS_ID, $orderId)
			->fetch();

		if ($row === false) {
			return [];
		}

		return [
			'id' => $row->cislo_objednavky,
			'timestamp' => $row->datum_cas,
			'customerId' => $row->zakaznicke_cislo,
			'customerName' => $row->ref(UserManager::TABLE_NAME, self::ORDERS_CUSTOMER)->jmeno,
			'customerSurname' => $row->ref(UserManager::TABLE_NAME, self::ORDERS_CUSTOMER)->prijmeni,
			'customerUsername' => $row->ref(UserManager::TABLE_NAME, self::ORDERS_CUSTOMER)->email,
			'customerStreet' => $row->ulice,
			'customerCity' => $row->mesto,
			'customerPostcode' => $row->psc,
			'state' => $row->stav,
			'paid' => $row->zaplaceno,
			'delivery' => $this->parameters['delivery'][$row->zpusob_doruceni]['name'],
			'deliveryPrice' => $this->parameters['delivery'][$row->zpusob_doruceni]['price'],
			'payment' => $this->parameters['payment'][$row->platebni_metoda]['name'],
			'paymentPrice' => $this->parameters['payment'][$row->platebni_metoda]['price'],
			'note' => $row->poznamka,
		];
	}

	/**
	 * Return array of ordered products in requested order.
	 * @param int $orderId
	 * @return array
	 */
	public function getOrderedProducts(int $orderId): array
	{
		$query = $this->database->table(self::TABLE_ORDERED)
			->where(self::ORDERED_ORDER, $orderId);

		$products = [];
		foreach ($query as $row) {
			$products[] = [
				'id' => $row->katalogove_cislo,
				'quantity' => $row->mnozstvi,
				'price' => $row->cena,
				'name' => $row->ref(ProductManager::TABLE_PRODUCT, self::ORDERED_PRODUCT)->nazev,
				'description' => $row->ref(ProductManager::TABLE_PRODUCT, self::ORDERED_PRODUCT)->popis,
			];
		}

		return $products;
	}

	/**
	 * Delete order witch equal ID.
	 * @param $orderId
	 * @return bool Return true in case of success.
	 */
	public function deleteOrder(int $orderId): bool
	{
		$this->database->beginTransaction();
		$ordered = $this->database->table(self::TABLE_ORDERED)->where(self::ORDERED_ORDER, $orderId)->delete();
		$orders = $this->database->table(self::TABLE_ORDERS)->where(self::ORDERS_ID, $orderId)->delete();
		$this->database->commit();

		return ($ordered AND $orders);
	}

	/**
	 * Return array of possible states of order.
	 * @return array
	 */
	public function getStates(): array
	{
		return [
			'čeká na vyřízení',
			'zboží odesláno',
			'připraveno k vyzvednutí',
			'objednávka vyřízena',
			'objednávka zrušena'
		];
	}

	/**
	 * Change state of selected order.
	 * @param int $orderId
	 * @param string $value
	 * @return bool Return true on success
	 */
	public function changeState(int $orderId, string $value): bool
	{
		return $this->database->table(self::TABLE_ORDERS)->where(self::ORDERS_ID, $orderId)->fetch()->update([
			self::ORDERS_STATE => $value
		]);
	}
}
