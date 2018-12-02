<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model\SupplierManager;
use Nette;
use App\Model\ProductManager;
use App\AdminModule\Forms\EditProductFormFactory;
use App\Model\UserManager;


class ProductsPresenter extends BasePresenter
{

	/**
	 * @var ProductManager $productManager
	 * @var UserManager $userManager
	 * @var int $orderId
	 */
	private $productManager, $supplierManager;

	public function __construct(ProductManager $productManager, SupplierManager $supplierManager)
	{
		parent::__construct();

		$this->productManager = $productManager;
		$this->supplierManager = $supplierManager;
	}

	public function renderDefault(): void
	{
		$this->template->products = $this->productManager->getProducts('', 0);
	}



	/**
	 * @secured
	 * @param string $id
	 * @throws Nette\Application\AbortException
	 */
	public function handleDelete(string $id): void
	{
		if ($this->productManager->deleteItem($id)) {
			$this->flashMessage('Produkt byl odstraněn');
		} else {
			$this->flashMessage('Produkt nebylo možné odstranit.','flash-error');
		}
		$this->redirect('Products:');
	}


    public function actionEdit(string $id = null): void
    {
        if ($id !== null) {
            $this->template->product = $this->productManager->getItem($id);
        }
    }

    public function createComponentEditProductForm(): Nette\Application\UI\Form
    {
        $form = new EditProductFormFactory($this, $this->productManager, $this->supplierManager, $this->template->product ?? null);
        return $form->create();
    }


}
