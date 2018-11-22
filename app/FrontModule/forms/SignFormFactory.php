<?php
declare(strict_types=1);

namespace App\FrontModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Security\User;


class SignFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var User $user
	 */
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
		$form->addText('username', 'Email:')
			->setRequired('Zadejte prosím email.')
			->addRule(Form::EMAIL, 'Zadejte platnou emailovou adresu.')
			->setAttribute('placeholder','Zadejte přihlašovací email')
			->setAttribute('class','form-input')
			->setAttribute('autofocus');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadejte prosím heslo.')
			->setAttribute('placeholder','Zadejte heslo')
			->setAttribute('class','form-input');

		$form->addCheckbox('remember', 'Neodhlašovat');

		$form->addSubmit('send', 'Přihlásit se')
			->setAttribute('class','submit');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values): void
	{
		if ($values->remember) {
			$this->user->setExpiration('14 days', FALSE);
		} else {
			$this->user->setExpiration('20 minutes', TRUE);
		}

		try {
			$this->user->login($values->username, $values->password);
		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

}
