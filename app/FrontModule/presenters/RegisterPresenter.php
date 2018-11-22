<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette;
use App\FrontModule\Forms\RegisterFormFactory;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;


class RegisterPresenter extends BasePresenter
{
	/**
	 * @var RegisterFormFactory $factory
	 */
	private $factory;

	public function __construct(RegisterFormFactory $factory)
	{
		parent::__construct();

		$this->factory = $factory;
	}

	/**
	 * User-data form factory.
	 * @return Form
	 */
	protected function createComponentUserData(): Form
	{
		$form = $this->factory->createForm();
		$form->onSuccess[] = [$this, 'formSucceeded'];
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
	 * @param Form $form
	 * @param ArrayHash $values
	 * @throws Nette\Application\AbortException
	 */
	public function formSucceeded(Form $form, ArrayHash $values): void
	{
		if ($this->factory->formSucceeded($form, $values) === TRUE) {
			try {
				$this->user->login($values->email, $values->password);
			} catch (Nette\Security\AuthenticationException $e) {
				$form->addError($e->getMessage());
			}
			$this->flashMessage('Byl jste úspěšně zaregistrován.');
			$this->redirect('Homepage:default');
		}
	}
}
