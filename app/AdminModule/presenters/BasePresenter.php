<?php

namespace App\AdminModule\Presenters;

use Nette;
use App\Model\Parameters;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	use \Nextras\Application\UI\SecuredLinksPresenterTrait;

	/** @var Parameters */
	protected $parameters;

	public function injectParameters(Parameters $parameters) {
		$this->parameters = $parameters->getParam();
	}

	protected function startup()
	{
		parent::startup();

		$this->user->getStorage()->setNamespace('Admin');

		if ($this->name != "Admin:Sign" && !$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		} elseif ($this->user->isLoggedIn() && !$this->user->isInRole('admin')) {
			$this->redirect(':Front:Sign:out');
		}
	}

	protected function createComponentNavbar()
	{
		$items = [
			'Objednávky' => ['Orders:default'],
			'Odhlásit se' => ['Sign:out'],
		];
		$control = new Navbar('Administrace', $items);
		return $control;
	}
}
