<?php
declare(strict_types=1);

namespace App\FrontModule\Forms;

use Nette;
use App\Model\OrderManager;
use Nette\Application\UI\Form;
use Nette\Http\Session;
use Nette\Utils\ArrayHash;


class BuyFormFactory
{
	use Nette\SmartObject;

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
	public function createForm(): Form
	{
		$form = new Form;

		$form->addGroup('Dodací údaje');
		$form->addText('street', 'Ulice a orientační číslo:')
			->setAttribute('class','form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setValue($this->session->street)
			->setRequired('Zadejte prosím ulici a orientační číslo.');

		$form->addText('city', 'Město:')
			->setAttribute('class', 'form-input')
			->addRule(Form::MAX_LENGTH,'Zadaný údaj je příliš dlouhý',45)
			->setValue($this->session->city)
			->setRequired('Zadejte prosím město.');

		$form->addText('zip', 'PSČ:')
			->setAttribute('class', 'form-input')
			->addRule(Form::PATTERN, 'PSČ ve tvaru 61200 nebo 612 00', '[0-9]{3}[ ]?[0-9]{2}')
			->setValue($this->session->zip)
			->setRequired('Zadejte prosím poštovní směrovací číslo.');

		$delivery = $this->orderManager->getDelivery();
		$deliveryList = [];
		foreach ($delivery as $id => $values) {
			$deliveryList[$id] = $values['name']. ' ('. $values['price'] .')';
		}
		if (!empty($deliveryList)) {
			$form->addGroup('Způsob dodání');
			$form->addRadioList('delivery','', $deliveryList)
				->setAttribute('class','form-radio')
				->setRequired('Zvolte způsob placení.')
				->setValue($this->session->delivery);
		}

		$payment = $this->orderManager->getPayment();
		$paymentList = [];
		foreach ($payment as $id => $values) {
			$paymentList[$id] = $values['name']. ' ('. $values['price'] .')';
		}
		if (!empty($paymentList)) {
			$form->addGroup('Způsob platby');
			$form->addRadioList('payment','', $paymentList)
				->setAttribute('class','form-radio')
				->setRequired('Zvolte způsob dopravy.')
				->setValue($this->session->payment);
		}

		$form->setCurrentGroup();

		$form->addTextArea('note','Poznámka k objednávce')
			->setAttribute('class','form-input')
			->setValue($this->session->note);

		$form->addCheckbox('terms','Souhlasím s obchodními podmínkami.')
			->setAttribute('class','form-radio')
			->setRequired('Chete-li pokračovat, musíte souhlasit s obchodními podmínkami');

		$form->addSubmit('deliverPay','Pokračovat v objednávce')
			->setAttribute('class','form-button');

		$form->onSuccess[] = [$this, 'onSuccess'];

		return $form;
	}

	public function onSuccess(Form $form, ArrayHash $values): void
	{
		$this->session->setExpiration('30 minutes');
		$this->session->note = $values->note;
		$this->session->street = $values->street;
		$this->session->city = $values->city;
		$this->session->zip = $values->zip;

		if (isset($values->delivery)) {
			$this->session->delivery = $values->delivery;
		} else {
			$this->session->delivery = NULL;
		}

		if (isset($values->payment)) {
			$this->session->payment = $values->payment;
		} else {
			$this->session->payment = NULL;
		}

		if (isset($this->session->back)) {
			unset($this->session->back);
		}
	}
}
