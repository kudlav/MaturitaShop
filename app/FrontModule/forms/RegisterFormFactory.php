<?php
declare(strict_types=1);

namespace App\FrontModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\EmailValidator;
use App\Model\Registrator;
use App\Model\DuplicateNameException;
use Nette\Utils\ArrayHash;


class RegisterFormFactory
{
	use Nette\SmartObject;

	private $emailValidator;
	private $registrator;

	public function __construct(EmailValidator $emailValidator, Registrator $registrator)
	{
		$this->emailValidator = $emailValidator;
		$this->registrator = $registrator;
	}

	public function createForm(): Form
	{
		$form = new Form;

		$form->addText('name','Jméno:')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setRequired('Zadejte prosím jméno');

		$form->addText('surname','Příjmení:')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setRequired('Zadejte prosím příjmení');
		$form->addText('email','Email:')
			->setAttribute('class','form-input')
			->addRule(Form::EMAIL, 'Zadejte platnou emailovou adresu.')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setRequired('Zadejte prosím email');

		$form->addPassword('password','Heslo:')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',100)
			->setRequired('Zadejte prosím heslo');

		$form->addCheckbox('terms','Souhlasím s obchodními podmínkami.')
			->setRequired('Musíte souhlasit s obchodními podmínkami');

		$form->addSubmit('confirm','Zaregistrovat')
			->setAttribute('class','form-button');

		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 * @return bool
	 */
	public function formSucceeded(Form $form, ArrayHash $values): bool
	{
		if ($this->emailValidator->validate($values->email)) {
			$form->addError('Zadaný email je již zaregistrován.');
			return false;
		} else {
			try {
				$this->registrator->add($values);
			} catch (DuplicateNameException $e) {
				$form->addError('Při registraci nastala chyba.');
				return false;
			}
			return true;
		}
	}
}
