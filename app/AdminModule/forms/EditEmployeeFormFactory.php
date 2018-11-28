<?php
declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use App\Model\EmployeeManager;


class EditEmployeeFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var Presenter $presenter
	 * @var EmployeeManager $employeeManager
	 * @var ActiveRow $employee
	 */
	private $presenter, $employee, $employeeManager;

	public function __construct(Presenter $presenter, EmployeeManager $employeeManager, ?ActiveRow $employee = null)
	{
		$this->presenter = $presenter;
		$this->employeeManager = $employeeManager;
		$this->employee = $employee;
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

		$form->addText('login','Uživatelské jméno: *')
			->addRule(Form::MAX_LENGTH, 'Uživatelské jméno nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte uživatelské jméno')
			->setAttribute('class', 'form-input')
		;

		$form->addText('firstname', 'Jméno: *')
			->addRule(Form::MAX_LENGTH, 'Jméno nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte jméno zaměstnance')
			->setAttribute('class', 'form-input')
		;

		$form->addText('surname', 'Příjmení: *')
			->addRule(Form::MAX_LENGTH, 'Příjmení nesmí mít více než 45 znaků', 45)
			->setRequired('Příjmení zaměstnance')
			->setAttribute('class', 'form-input')
		;

		$form->addSelect('role', 'Role: *', ['prodejce' => 'prodejce', 'spravce' => 'spravce'])
			->setRequired("Zvolte uživatelskou úroveň")
			->setAttribute('class', 'form-input')
		;

		$form->addPassword('password', 'Nové heslo:')
			->setAttribute('class', 'form-input')
		;

		if ($this->employee !== null) {
			$form->getComponent('login')->setDisabled(true)->setValue($this->employee->uzivatelske_jmeno);
			$form->getComponent('firstname')->setValue($this->employee->jmeno);
			$form->getComponent('surname')->setValue($this->employee->prijmeni);
			$form->getComponent('role')->setValue($this->employee->role);
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
		if ($this->employee === null) { // New employee
			if ($this->employeeManager->addEmployee($form->getValues(true))) {
				$this->presenter->flashMessage('Zaměstnanec byl uložen');
			}
		}
		else { // Edit existing employee
			if ($this->employeeManager->updateEmployee((string)$this->employee->uzivatelske_jmeno, $form->getValues(true))) {
				$this->presenter->flashMessage('Zaměstnanec byl uložen');
			}
		}
		$this->presenter->redirect('Employees:default');
	}
}
