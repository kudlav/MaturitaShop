<?php
declare(strict_types=1);

namespace App\AdminModule\Forms;

use App\Model\SupplierManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\IRow;
use Nette\Utils\ArrayHash;


class EditSupplierFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var Presenter $presenter
	 * @var SupplierManager $supplierManager
	 * @var IRow $supplier
	 */
	private $presenter, $supplier, $supplierManager;

	public function __construct(Presenter $presenter, SupplierManager $supplierManager, ?IRow $supplier = null)
	{
		$this->presenter = $presenter;
		$this->supplierManager = $supplierManager;
		$this->supplier = $supplier;
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

		$form->addText('id','IČO: *')
			->addRule(Form::PATTERN, 'IČO musí mít 8 číslic', '([0-9]\s*){8}')
			->setRequired('Zadejte identifikační číslo osoby')
			->setAttribute('class', 'form-input')
		;

		$form->addText('name', 'Název: *')
			->addRule(Form::MAX_LENGTH, 'Název nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte název dodavatele')
			->setAttribute('class', 'form-input')
		;

		$form->addText('delivery', 'Doba dodání: *')
			->addRule(Form::MAX_LENGTH, 'Doba dodání nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte dobu dodání')
			->setAttribute('class', 'form-input')
			->setAttribute('placeholder', 'např.: 14 dní')
		;

		$form->addGroup('Adresa');

		$form->addText('street', 'Ulice: *')
			->addRule(Form::MAX_LENGTH, 'Ulice nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte ulici a číslo orientační')
			->setAttribute('class', 'form-input')
		;

		$form->addText('city', 'Město: *')
			->addRule(Form::MAX_LENGTH, 'Město nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte město')
			->setAttribute('class', 'form-input')
		;

		$form->addText('zip', 'PSČ: *')
			->addRule(Form::PATTERN, 'PSČ ve tvaru 61200 nebo 612 00', '([0-9]\s?){5}')
			->setRequired('Zadejte poštovní směrovací číslo.')
			->setAttribute('class', 'form-input')
		;

		$form->addGroup('Kontakt');

		$form->addText('fullname', 'Kontaktní osoba: *')
			->addRule(Form::MAX_LENGTH, 'Jméno osoby nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte jméno kontaktní osoby')
			->setAttribute('class', 'form-input')
		;

		$form->addText('phone','Telefon:')
			->setAttribute('class','form-input')
			->addRule(Form::PATTERN, 'Číslo zadejte např. ve tvaru +420 765 246 265', '([\+]?\d{3})?([ ]?\d{3}){3}')
			->setRequired(false)
		;

		$form->addText('email','Email:')
			->setAttribute('class','form-input')
			->addRule(Form::EMAIL, 'Zadejte platnou emailovou adresu.')
			->addRule(Form::MAX_LENGTH,'Zadaná emailová adresa je příliš dlouhá, max. 45 znaků.',45)
			->setRequired(false)
		;

		if ($this->supplier !== null) {
			$form->getComponent('id')->setDisabled(true)->setValue($this->supplier->ico);
			$form->getComponent('name')->setValue($this->supplier->nazev);
			$form->getComponent('delivery')->setValue($this->supplier->dodaci_lhuta);
			$form->getComponent('street')->setValue($this->supplier->ulice);
			$form->getComponent('city')->setValue($this->supplier->mesto);
			$form->getComponent('zip')->setValue($this->supplier->psc);
			$form->getComponent('fullname')->setValue($this->supplier->kontaktni_osoba);
			$form->getComponent('phone')->setValue($this->supplier->telefon);
			$form->getComponent('email')->setValue($this->supplier->email);
		}

		$form->onValidate[] = [$this, 'validate'];
		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	public function validate(Form $form): void
	{
		$values = $form->getValues();
		if ($this->supplier === null) {
			if ($this->supplierManager->getSupplier((int)$values['id'])) {
				$form['id']->addError('Dodavatel se shodným IČ již existuje.');
			}
		}
	}

	/**
	 * @param Form $form
	 * @throws Nette\Application\AbortException
	 */
	public function formSucceeded(Form $form): void
	{
		if ($this->supplier === null) { // New supplier
			$this->supplierManager->addSupplier($form->getValues(true));
			$this->presenter->flashMessage('Dodavatel byl uložen');
		}
		else { // Edit existing supplier
			$this->supplierManager->updateSupplier((int)$this->supplier->ico, $form->getValues(true));
		}
		$this->presenter->redirect('Suppliers:default');
	}
}
