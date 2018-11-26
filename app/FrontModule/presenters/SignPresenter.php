<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use App\Model\CartManager;
use Nette;
use App\FrontModule\Forms\SignFormFactory;
use Nette\Application\UI\Form;


class SignPresenter extends BasePresenter
{
	/** @persistent */
	public $state= '';

	/**
	 * @var SignFormFactory $factory
	 * @var CartManager $cartManager
	 */
	private $factory, $cartManager;

	public function __construct(SignFormFactory $factory, CartManager $cartManager)
	{
		parent::__construct();

		$this->factory = $factory;
		$this->cartManager = $cartManager;
	}

	/**
	 * Sign-in form factory.
	 * @return Form
	 */
	protected function createComponentSignInForm(): Form
	{
		$form = $this->factory->create();
		$form->onSuccess[] = function () {
			// Successfully logged in, move cart from cookies to DB
			$cartCookies = $this->getHttpRequest()->getCookie('cart');
			if ($cartCookies !== null) {
				$cart = json_decode($cartCookies, true);
				foreach ($cart as $id => $count) {
					if ($count > 0) {
						for ($i = 0; $i < $count; $i++) {
							$this->cartManager->addItem($this->user->id, $id, 0);
						}
					}
				}
				$this->getHttpResponse()->deleteCookie('cart');
			}
			$this->restoreRequest($this->state);
			$this->redirect('Homepage:');
		};
		return $form;
	}

	/**
	 * @return Navbar
	 */
	protected function createComponentNavbar(): Navbar
	{
		if ($this->getUser()->isLoggedIn()) {
			$items = $this->parameters['logged_menu'];
		} else {
			$items = [
				'Košík' => ['User:cart'],
				'Přihlásit se' => ['Sign:in', $this->storeRequest()],
				'Zaregistrovat se' => ['Register:default'],
			];
		}
		$control = new Navbar('Můj účet', $items);
		return $control;
	}

	/**
	 * @throws Nette\Application\AbortException
	 */
	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen.');
		$this->redirect('Homepage:default');
	}

}
