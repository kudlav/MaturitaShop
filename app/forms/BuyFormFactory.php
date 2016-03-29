<?php

namespace App\Forms;

use Nette;
use Nette\Application\UI\Form;


class BuyFormFactory extends Nette\Object
{
	const
		DELIVERY_METHODS = ['Česká pošta (99 Kč)','PPL (99 Kč)', 'Uloženka (20 Kč)'],
		PAYMENT_METHODS = ['Převodem předem (0 Kč)','Kartou online (99 Kč)', 'Dobírkou při převzetí (49 Kč)'];

	/**
	 * @return Form
	 */
	public function createForm()
	{
		$form = new Form;

		$form->addGroup('Způsob dodání');
		$form->addRadioList('delivery','', self::DELIVERY_METHODS)
			->setAttribute('class','form-radio')
			->setRequired('Zvolte způsob placení.');

		$form->addGroup('Způsob platby');
		$form->addRadioList('payment','', self::PAYMENT_METHODS)
			->setAttribute('class','form-radio')
			->setRequired('Zvolte způsob dopravy.');;

		$form->setCurrentGroup();

		$form->addTextArea('note','Poznámka k objednávce')
			->setAttribute('class','form-input');

		$form->addSubmit('deliverPay','Pokračovat v objednávce')
			->setAttribute('class','form-button');

		return $form;
	}
}
