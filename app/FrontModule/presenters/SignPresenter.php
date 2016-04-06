<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\FrontModule\Forms\SignFormFactory;


class SignPresenter extends BasePresenter
{
	/** @persistent */
	public $state= '';

	/** @var SignFormFactory */
	private $factory;


	public function __construct(SignFormFactory $factory)
	{
		$this->factory = $factory;
	}


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->factory->create();
		$form->onSuccess[] = function () {
			$this->restoreRequest($this->state);
			$this->redirect('Homepage:');
		};
		return $form;
	}


	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen.');
		$this->redirect('Homepage:default');
	}

}
