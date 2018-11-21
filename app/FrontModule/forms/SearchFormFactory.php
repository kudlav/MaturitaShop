<?php

namespace App\FrontModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\FrontModule\Model\ProductManager;
use Nette\Application\UI\Presenter;


class SearchFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var ProductManager
	 */
	private $productManager;

	/**
	 * @var Presenter
	 */
	private $presenter;

	public function __construct(ProductManager $productManager, Presenter $presenter)
	{
		$this->productManager = $productManager;
		$this->presenter = $presenter;
	}

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form();

		$form->addText('searchTerm')
			->setType('search')
			->setAttribute('placeholder', 'Hledaný produkt...');

		$form->addSubmit('send','Hledat')
			->setAttribute('class', 'button');

		$form->onSuccess[] = array($this, 'formSucceeded');

		return $form;
	}


	public function formSucceeded(Form $form, $values)
	{
		$this->presenter->redirect('Homepage:default', array('search' => $values->searchTerm));
	}
}
