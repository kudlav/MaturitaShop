<?php

namespace App\FrontModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Http\Session;


class BuyFormFactory extends Nette\Object
{
	const
		DELIVERY_METHODS = ['Česká pošta (99 Kč)','PPL (99 Kč)', 'Uloženka (20 Kč)'],
		PAYMENT_METHODS = ['Převodem předem (0 Kč)','Kartou online (99 Kč)', 'Dobírkou při převzetí (49 Kč)'];

	/** @var Session */
	private $session;

	public function __construct(Session $session)
	{
		$this->session = $session->getSection('buy');
	}

	/**
	 * @return Form
	 */
	public function createForm()
	{
		$form = new Form;

		$form->addGroup('Způsob dodání');
		$form->addRadioList('delivery','', self::DELIVERY_METHODS)
			->setAttribute('class','form-radio')
			->setRequired('Zvolte způsob placení.')
			->setValue($this->session->delivery);

		$form->addGroup('Způsob platby');
		$form->addRadioList('payment','', self::PAYMENT_METHODS)
			->setAttribute('class','form-radio')
			->setRequired('Zvolte způsob dopravy.')
			->setValue($this->session->payment);

		$form->setCurrentGroup();

		$form->addTextArea('note','Poznámka k objednávce')
			->setAttribute('class','form-input')
			->setValue($this->session->note);

		$form->addCheckbox('terms','Souhlasím s obchodními podmínkami.')
			->setAttribute('class','form-radio')
			->setRequired('Musíte souhlasit s obchodními podmínkami');

		$form->addSubmit('deliverPay','Pokračovat v objednávce')
			->setAttribute('class','form-button');

		$form->onSuccess[] = array($this, 'onSuccess');

		return $form;
	}

	public function onSuccess(Form $form, $values) {
		$this->session->setExpiration('30 minutes');
		$this->session->delivery = $values->delivery;
		$this->session->payment = $values->payment;
		$this->session->note = $values->note;

		if (isset($this->session->back)) {
			unset($this->session->back);
		}
	}
}
