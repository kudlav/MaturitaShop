<?php

namespace App\FrontModule\Forms;

use App\FrontModule\Model\OrderManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Http\Session;


class BuyFormFactory extends Nette\Object
{

	/**
	 * @var Session $session
	 * @var OrderManager $orderManager
	 */
	private $session, $orderManager;

	public function __construct(Session $session, OrderManager $orderManager)
	{
		$this->session = $session->getSection('buy');
		$this->orderManager = $orderManager;
	}

	/**
	 * @return Form
	 */
	public function createForm()
	{
		$form = new Form;
		$form->addGroup('Způsob dodání');
		$form->addRadioList('delivery','', $this->orderManager->getDelivery())
			->setAttribute('class','form-radio')
			->setRequired('Zvolte způsob placení.')
			->setValue($this->session->delivery);

		$form->addGroup('Způsob platby');
		$form->addRadioList('payment','', $this->orderManager->getPayment())
			->setAttribute('class','form-radio')
			->setRequired('Zvolte způsob dopravy.')
			->setValue($this->session->payment);

		$form->setCurrentGroup();

		$form->addTextArea('note','Poznámka k objednávce')
			->setAttribute('class','form-input')
			->setValue($this->session->note);

		$form->addCheckbox('terms','Souhlasím s obchodními podmínkami.')
			->setAttribute('class','form-radio')
			->setRequired('Chete-li pokračovat, musíte souhlasit s obchodními podmínkami');

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
