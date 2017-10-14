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
		parent::__construct();

		$this->factory = $factory;
	}

	protected function startup() {
		parent::startup();

		if (!$this->parameters['eshop']) {
			$this->error(); //Error 404
		}
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
