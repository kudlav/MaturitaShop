<?php
declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use App\Model\OrderManager;
use Nette\Utils\ArrayHash;


class ChangeStateFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var Presenter $presenter
	 * @var OrderManager $orderManager
	 * @var array $orders
	 * @var array $states
	 */
	private $presenter, $orderManager, $orders, $states;

	public function __construct(Presenter $presenter, OrderManager $orderManager, $orders, $states)
	{
		$this->presenter = $presenter;
		$this->orderManager = $orderManager;
		$this->orders = $orders;
		$this->states = $states;
	}

	/**
	 * @return Form
	 */
	public function create(): Form
	{
		$form = new Form;

		foreach ($this->orders as $order) {
			$form->addSelect('s'.$order['id'],'Stav objednávky',$this->states)
				->setValue(array_search($order['state'], $this->states))
				->setRequired();
		}

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	public function formSucceeded(Form $form, ArrayHash $values): void
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
