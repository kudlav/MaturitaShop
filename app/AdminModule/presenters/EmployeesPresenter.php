<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\EditEmployeeFormFactory;
use Nette;
use App\Model\EmployeeManager;
use Nette\Application\UI\Form;


class EmployeesPresenter extends BasePresenter
{

	/**
	 * @var EmployeeManager $employeeManager
	 */
	private $employeeManager;

	public function __construct(EmployeeManager $employeeManager)
	{
		parent::__construct();

		$this->employeeManager = $employeeManager;
	}

	public function renderDefault(): void
	{
		$this->template->employees = $this->employeeManager->getEmployees();
	}

	/**
	 * @secured
	 */
	public function handleDisable(string $id): void
	{
		if ($this->employeeManager->disableEmployee($id)) {
			$this->flashMessage("Účet zaměstnance $id byl deaktivován");
		} else {
			$this->flashMessage("Účet zaměstnance $id nebylo možné deaktivovat",'flash-error');
		}
		$this->redirect('Employees:default');
	}

	/**
	 * @secured
	 */
	public function handleDelete(string $id): void
	{
		if ($this->employeeManager->deleteEmployee($id)) {
			$this->flashMessage("Účet zaměstnance $id byl odstraněn");
		} else {
			$this->flashMessage("Účet zaměstnance $id nebylo možné odstranit",'flash-error');
		}
		$this->redirect('Employees:default');
	}

	public function actionEdit(string $id = null): void
	{
		if ($id !== null) {
			$this->template->employee = $this->employeeManager->getEmployee($id);
		}
	}

	public function createComponentEditEmployeeForm(): Form
	{
		$form = new EditEmployeeFormFactory($this, $this->employeeManager, $this->template->employee ?? null);
		return $form->create();
	}
}
