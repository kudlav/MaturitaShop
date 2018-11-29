<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;
use App\AdminModule\Forms\EditCustomerFormFactory;
use App\Model\UserManager;
use Nette\Application\UI\Form;


class CustomersPresenter extends BasePresenter
{

	/**
	 * @var UserManager $userManager
	 */
	private $userManager;

	public function __construct(UserManager $userManager)
	{
		parent::__construct();

		$this->userManager = $userManager;
	}

	public function renderDefault(): void
	{
		$this->template->customers = $this->userManager->getUsers();
	}

	/**
	 * @secured
	 */
	public function handleDelete(int $id): void
	{
		if ($this->userManager->deleteUser($id)) {
			$this->flashMessage('Zákazník byl odstraněn');
		} else {
			$this->flashMessage('Zákazníka nebylo možné odstranit, patrně již provedl objednávku.','flash-error');
		}
		$this->redirect('Customers:');
	}

	public function actionEdit(int $id = null): void
	{
		if ($id !== null) {
			$this->template->customer = $this->userManager->getUser($id);
		}
	}

	public function createComponentEditCustomerForm(): Form
	{
		$form = new EditCustomerFormFactory($this, $this->userManager, $this->template->customer ?? null);
		return $form->create();
	}

	public function actionContact(int $id): void
	{

	}

	public function createComponentContact(): Contact
	{
		$customer = $this->userManager->getUser((int)$this->getParameter('id'));
		$data = [
			'person' => $customer->jmeno .' '. $customer->prijmeni,
			'email' => $customer->email,
			'street' => $customer->ulice,
			'city' => $customer->mesto,
			'zip' => $customer->psc,
		];
		$control = new Contact($data, $this->parameters['contact']['email_from']);
		return $control;
	}

	/**
	 * @secured
	 */
	public function handleDisable(string $id): void
	{
		if ($this->userManager->disableUser($id)) {
			$this->flashMessage("Účet zákazníka byl deaktivován");
		} else {
			$this->flashMessage("Účet zákazníka nebylo možné deaktivovat",'flash-error');
		}
		$this->redirect('Customers:');
	}
}
