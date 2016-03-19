<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;
use App\Model\EmailValidator;
use App\Model\Registrator;
use App\Model\DuplicateNameException;


class UserDataFormFactory extends Nette\Object
{
	private $emailValidator;
	private $registrator;

	public function __construct(EmailValidator $emailValidator, Registrator $registrator)
	{
		$this->emailValidator = $emailValidator;
		$this->registrator = $registrator;
	}

	/**
	 * @return Form
	 */
	public function create()
	{

	}

	public function createForm()
	{
		$form = new Form;

		$form->addText('email','Email: *')
			->setAttribute('class','form-input')
			->setType('email')
			->addRule(Form::EMAIL, 'Zadejte platnou emailovou adresu.')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setRequired('Zadejte prosím email');

		$form->addPassword('password','Heslo: *')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',100)
			->setRequired('Zadejte prosím heslo');

		$form->addText('name','Jméno: *')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setRequired('Zadejte prosím jméno');

		$form->addText('surname','Příjmení: *')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setRequired('Zadejte prosím příjmení');

		$form->addText('street','Ulice, č.p.:')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',50);

		$form->addText('city','Město:')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45);

		$form->addText('postcode','PSČ:')
			->setAttribute('class','form-input')
			->addCondition(Form::FILLED)
			->addRule(Form::PATTERN, 'PSČ ve tvaru 61200 nebo 612 00', '[0-9]{3}[ ]?[0-9]{2}');

		$form->addCheckbox('terms','Souhlasím s obchodními podmínkami.')
			->setRequired('Musíte souhlasit s obchodními podmínkami.');

		$form->addSubmit('confirm','Zaregistrovat')
			->setAttribute('class','form-button');

		return $form;
	}

	/**
	 * @param Form $form
	 * @param Nette\Utils\ArrayHash $values
	 * @return bool
	 */
	public function formSucceeded(Form $form, $values)
	{
		if ($this->emailValidator->validate($values->email)) {
			$form->addError('Zadaný email je již zaregistrován.');
			return FALSE;
		} else {
			try {
				$this->registrator->add($values);
			} catch (DuplicateNameException $e) {
				$form->addError('Při registraci nastala chyba.');
				return FALSE;
			}
			return TRUE;
		}
	}

}
