<?php
declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\Row;
use App\Model\UserManager;


class EditCustomerFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var Presenter $presenter
	 * @var UserManager $userManager
	 * @var Row $customer
	 */
	private $presenter, $customer, $userManager;

	public function __construct(Presenter $presenter, UserManager $userManager, ?Row $customer = null)
	{
		$this->presenter = $presenter;
		$this->userManager = $userManager;
		$this->customer = $customer;
	}

	/**
	 * @return Form
	 */
	public function create(): Form
	{
		$form = new Form;

		$form->addSubmit('submit', 'Uložit změny')
			->setAttribute('class', 'form-button button')
		;

		$form->addGroup();

		$form->addText('firstname', 'Jméno: *')
			->addRule(Form::MAX_LENGTH, 'Jméno nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte jméno zaměstnance')
			->setAttribute('class', 'form-input')
		;

		$form->addText('surname', 'Příjmení: *')
			->addRule(Form::MAX_LENGTH, 'Příjmení nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte příjmení zaměstnance')
			->setAttribute('class', 'form-input')
		;

		$form->addText('email','Email: *')
			->setAttribute('class','form-input')
			->addRule(Form::EMAIL, 'Zadejte platnou emailovou adresu.')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setRequired('Zadejte prosím email');

		;

		$form->addPassword('password', 'Nové heslo:')
			->setAttribute('class', 'form-input')
		;

		if ($this->customer !== null) {
			$form->getComponent('firstname')->setValue($this->customer->jmeno);
			$form->getComponent('surname')->setValue($this->customer->prijmeni);
			$form->getComponent('email')->setValue($this->customer->email);
		}

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	/**
	 * @param Form $form
	 * @throws Nette\Application\AbortException
	 */
	public function formSucceeded(Form $form): void
	{
		if ($this->customer === null) { // New customer
			if ($this->userManager->addUser($form->getValues(true))) {
				$this->presenter->flashMessage("Zákazník byl uložen");
			}
		}
		else { // Edit existing customer
			if ($this->userManager->updateUser((string)$this->customer->zakaznicke_cislo, $form->getValues(true))) {
				$this->presenter->flashMessage('Zákazník byl uložen');
			}
		}
		$this->presenter->redirect('Customers:');
	}
}
