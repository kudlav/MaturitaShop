<?php

namespace App\FrontModule\Presenters;

use Nette;
use App\FrontModule\Model;
use App\FrontModule\Forms\UserDataFormFactory;
use Nette\Application\UI\Form;


class RegisterPresenter extends BasePresenter
{
	/** @var UserDataFormFactory */
	private $factory;

	public function __construct(UserDataFormFactory $factory)
	{
		$this->factory = $factory;
	}


	/**
	 * User-data form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentUserData()
	{
		$form = $this->factory->createForm();
		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	/**
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 */
	public function formSucceeded(Form $form, $values)
	{
		if ($this->factory->formSucceeded($form, $values) === TRUE) {
			try {
				$this->user->login($values->email, $values->password);
			} catch (Nette\Security\AuthenticationException $e) {
				$form->addError($e->getMessage());
			}
			$this->flashMessage('Byl jste úspěšně zaregistrován.');
			$this->redirect('Homepage:default');
		}
	}
}
