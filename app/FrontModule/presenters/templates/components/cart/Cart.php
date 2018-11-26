<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\Model\ProductManager;
use Nette;
use Nette\Application\UI\Control;
use App\Model\CartManager;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Security\User;


class Cart extends Control
{

	/**
	 * @var User $user
	 * @var CartManager $cartManager
	 * @var ProductManager $productManager
	 * @var IRequest $request
	 * @var IResponse $response
	 */
	private $user, $cartManager, $productManager, $request, $response;

	public function __construct(User $user, CartManager $cartManager, ProductManager $productManager, Irequest $request, Iresponse $response)
	{
		parent::__construct();

		$this->user = $user;
		$this->cartManager = $cartManager;
		$this->productManager = $productManager;
		$this->request = $request;
		$this->response = $response;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/cart.latte');
		$template->cart = $this->getCounts();

		$template->render();
	}

	/**
	 * Gets the count and price of items in user cart.
	 * @return array with 'count' and 'price'
	 */
	private function getCounts(): array
	{
		$ret = [
			'count' => 0,
			'price' => 0,
		];

		if ($this->user->isLoggedIn()) { // Get from DB
			$ret['count'] = $this->cartManager->getCount($this->user->getId());
			$ret['price'] = $this->cartManager->getPrice($this->user->getId());
		}
		else { // Get form cookies
			$cartCookies = $this->request->getCookie('cart');
			if ($cartCookies !== null) {
				$cart = json_decode($cartCookies, true);
				foreach ($cart as $id => $count) {
					$product = $this->productManager->getItem($id);
					if ($product !== null AND $product->zobrazovat == 1) {
						$ret['count'] += $count;
						$ret['price'] += $count * $product->cena;
					}
				}
			}
		}
		return $ret;
	}
}
