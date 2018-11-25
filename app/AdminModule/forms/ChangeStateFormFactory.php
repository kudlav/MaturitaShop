<?php
declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use App\Model\OrderManager;
use Nette\Database\ResultSet;
use Nette\Utils\ArrayHash;


class ChangeStateFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var Presenter $presenter
	 * @var OrderManager $orderManager
	 * @var ResultSet $orders
	 * @var array $states
	 */
	private $presenter, $orderManager, $orders, $states;

	public function __construct(Presenter $presenter, OrderManager $orderManager, ResultSet $orders, array $states)
	{
		$this->presenter = $presenter;
		$this->orderManager = $orderManager;
		$this->orders = $orders->fetchPairs('cislo_objednavky', 'stav');;
		$this->states = $states;
	}

	/**
	 * @return Form
	 */
	public function create(): Form
	{
		$form = new Form;

		foreach ($this->orders as $id => $state) {
			$form->addSelect('s'.$id, 'Stav objednávky', $this->states)
				->setValue(array_search($state, $this->states))
				->setRequired();
		}

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values): void
	{
		if ($this->presenter->isAjax()) {
			foreach ($values as $key => $value) {
				$orderId = intval(str_replace('s','',$key));
				if ($this->states[$value] !== $this->orders[$orderId]) {
					$this->orderManager->changeState($orderId, $this->states[$value]);
				}
			}
			$this->presenter->redrawControl('ordersList');
		}
	}
}
