<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\EditSupplierFormFactory;
use Nette;
use App\Model\SupplierManager;
use Nette\Application\UI\Form;


class SuppliersPresenter extends BasePresenter
{

	/**
	 * @var SupplierManager $supplierManager
	 */
	private $supplierManager;

	public function __construct(SupplierManager $supplierManager)
	{
		parent::__construct();

		$this->supplierManager = $supplierManager;
	}

	public function renderDefault(): void
	{
		$this->template->suppliers = $this->supplierManager->getSuppliers();
	}

	/**
	 * @secured
	 */
	public function handleDelete(int $id): void
	{
		if ($this->supplierManager->deleteSupplier($id)) {
			$this->flashMessage('Dodavatel byl odstraněn');
		} else {
			$this->flashMessage('Dodavatele nebylo možné odstranit, patrně dodává některé produkty.','flash-error');
		}
		$this->redirect('Suppliers:');
	}

	public function actionEdit(int $id = null): void
	{
		if ($id !== null) {
			$this->template->suppliers = $this->supplierManager->getSupplier($id);
		}
	}

	public function createComponentEditSupplierForm(): Form
	{
		$form = new EditSupplierFormFactory($this, $this->supplierManager, $this->template->suppliers ?? null);
		return $form->create();
	}

	public function actionContact(int $id): void
	{

	}

	public function createComponentContact(): Contact
	{
		$supplier = $this->supplierManager->getSupplier((int)$this->getParameter('id'));
		$data = [
			'company' => $supplier->nazev,
			'person' => $supplier->kontaktni_osoba,
			'email' => $supplier->email,
			'street' => $supplier->ulice,
			'city' => $supplier->mesto,
			'zip' => $supplier->psc,
		];
		$control = new Contact($data, $this->parameters['contact']['email_from']);
		return $control;
	}
}
