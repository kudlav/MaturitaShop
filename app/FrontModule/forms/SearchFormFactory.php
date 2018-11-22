<?php
declare(strict_types=1);

namespace App\FrontModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\ProductManager;
use Nette\Application\UI\Presenter;
use Nette\Utils\ArrayHash;


class SearchFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var ProductManager $productManager
	 * @var Presenter $presenter
	 */
	private $productManager, $presenter;

	public function __construct(ProductManager $productManager, Presenter $presenter)
	{
		$this->productManager = $productManager;
		$this->presenter = $presenter;
	}

	/**
	 * @return Form
	 */
	public function create(): Form
	{
		$form = new Form();

		$form->addText('searchTerm')
			->setType('search')
			->setAttribute('placeholder', 'Hledaný produkt...');

		$form->addSubmit('send','Hledat')
			->setAttribute('class', 'button');

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}


	public function formSucceeded(Form $form, ArrayHash $values): void
	{
		$this->presenter->redirect('Homepage:default', array('search' => $values->searchTerm));
	}
}
