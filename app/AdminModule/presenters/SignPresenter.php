<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use Nette;
use App\AdminModule\Forms\SignFormFactory;
use Nette\Application\UI\Form;


class SignPresenter extends BasePresenter
{
	/** @persistent */
	public $state= '';

	/**
	 * @var SignFormFactory $factory
	 */
	private $factory;

	public function __construct(SignFormFactory $factory)
	{
		parent::__construct();

		$this->factory = $factory;
	}

	/**
	 * Sign-in form factory.
	 * @return Form
	 */
	protected function createComponentSignInForm(): Form
	{
		$form = $this->factory->create();
		$form->onSuccess[] = function () {
			$this->restoreRequest($this->state);
			$this->redirect('Homepage:');
		};
		return $form;
	}


	/**
	 * @throws Nette\Application\AbortException
	 */
	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('Byl jste odhlášen.');
		$this->redirect('Homepage:');
	}

}
