<?php
declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;
use Nette\Utils\ArrayHash;


class SignFormFactory
{
	use Nette\SmartObject;

	/** @var User */
	private $user;

	public function __construct(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return Form
	 */
	public function create(): Form
	{
		$form = new Form;
		$form->addText('username', 'Uživatel:')
			->setRequired('Zadejte uživatelské jméno.')
			->addRule(Form::PATTERN, 'Uživatelské jméno není nikdy email!',('[^@]*'))
			->setAttribute('placeholder','Zadejte uživatelské jméno')
			->setAttribute('oninvalid','setCustomValidity("Uživatelské jméno není nikdy email")')
			->setAttribute('class','form-input')
			->setAttribute('autofocus');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadejte prosím heslo.')
			->setAttribute('placeholder','Zadejte heslo')
			->setAttribute('class','form-input');

		$form->addSubmit('send', 'Přihlásit se')
			->setAttribute('class','submit');

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}


	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 */
	public function formSucceeded(Form $form, ArrayHash $values): void
	{
		$this->user->setExpiration('20 minutes', TRUE);

		try {
			$this->user->login($values->username, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

}
