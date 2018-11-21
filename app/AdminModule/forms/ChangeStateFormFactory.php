<?php

namespace App\AdminModule\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Application\UI\Presenter,
	App\FrontModule\Model\OrderManager;


class ChangeStateFormFactory
{
	use Nette\SmartObject;

	/** @var Presenter */
	private $presenter;

	/** @var array */
	private $orders;

	/** @var OrderManager */
	private $orderManager;

	/** @var array */
	private $states;

	public function __construct(Presenter $presenter, OrderManager $orderManager, $orders, $states)
	{
		$this->presenter = $presenter;
		$this->orders = $orders;
		$this->orderManager = $orderManager;
		$this->states = $states;
	}

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;

		foreach ($this->orders as $order) {
			$form->addSelect('s'.$order['id'],'Stav objednávky',$this->states)
				->setValue(array_search($order['state'], $this->states))
				->setRequired();
		}

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		if ($this->presenter->isAjax()) {
			$i = 0;
			foreach ($values as $key => $value) {
				if ($this->states[$value] !== $this->orders[$i]['state']) {
					$this->orderManager->changeState(str_replace('s','',$key), $this->states[$value]);
				}
				$i++;
			}
			$this->presenter->redrawControl('ordersList');
		}
	}
}
