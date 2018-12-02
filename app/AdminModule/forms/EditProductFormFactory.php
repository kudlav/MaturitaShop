<?php
declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Database\Table\ActiveRow;
use App\Model\productManager;
use App\Model\SupplierManager;


class EditProductFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var Presenter $presenter
	 * @var productManager $productManager
	 * @var ActiveRow $product
	 */
	private $presenter, $product, $productManager, $supplierManager;

	public function __construct(Presenter $presenter, productManager $productManager, supplierManager $supplierManager, ?ActiveRow $product = null)
	{
		$this->presenter = $presenter;
		$this->productManager = $productManager;
		$this->supplierManager = $supplierManager;
		$this->product = $product;
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

		$form->addText('id','Katalogové číslo: *')
			->addRule(Form::MAX_LENGTH, 'Uživatelské jméno nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte uživatelské jméno')
			->setAttribute('class', 'form-input')
		;

		$form->addText('name', 'Název: *')
			->addRule(Form::MAX_LENGTH, 'Název nesmí mít více než 45 znaků', 45)
			->setRequired('Zadejte název produktu')
			->setAttribute('class', 'form-input')
		;

		$form->addText('description', 'Popis: ')
			->addRule(Form::MAX_LENGTH, 'Popis nesmí mít více než 45 znaků', 255)
            ->setRequired(false)
			->setAttribute('class', 'form-input')
		;

		$form->addInteger('price', 'Cena: *')
			->setRequired("Zvolte cenu produktu")
			->setAttribute('class', 'form-input')
		;

		$form->addInteger('stock', 'Množství skladem: *')
            ->setRequired("Zvolte množství skladem")
            ->setAttribute('class', 'form-input')
		;

        $form->addSelect('category', 'Kategorie: ', ['Skicaky' => 'Skicáky', 'Pastelky' => 'Pastelky'])
            ->addRule(Form::MAX_LENGTH, 'Kategorie nesmí mít více než 45 znaků', 45)
            ->setRequired(false)
            ->setAttribute('class', 'form-input')
        ;

        $form->addSelect('supplier', 'Dodavatel: *', iterator_to_array($this->supplierManager->getSuppliers()))
            ->setRequired('Zvolte dodavatele.')
            ->setAttribute('class', 'form-input')
        ;

        $form->addCheckbox('show', 'Zobrazovat')
            ->setRequired(false)
            ->setDefaultValue(true)
        ;

        if ($this->product !== null) {
			$form->getComponent('id')->setDisabled(true)->setValue($this->product->katalogove_cislo);
			$form->getComponent('name')->setValue($this->product->nazev);
			$form->getComponent('price')->setValue($this->product->cena);
			$form->getComponent('stock')->setValue($this->product->mnozstvi_skladem);
			$form->getComponent('category')->setValue($this->product->kategorie);
            $form->getComponent('supplier')->setValue($this->product->dodavatel);
            $form->getComponent('show')->setValue($this->product->zobrazovat);
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
		if ($this->product === null) { // New product
			if ($this->productManager->addProduct($form->getValues(true))) {
				$this->presenter->flashMessage('Produkt byl uložen');
			}
		}
		else { // Edit existing product
			if ($this->productManager->updateProduct((string)$this->product->katalogove_cislo, $form->getValues(true))) {
				$this->presenter->flashMessage('Produkt byl uložen');
			}
		}
		$this->presenter->redirect('Products:default');
	}
}
