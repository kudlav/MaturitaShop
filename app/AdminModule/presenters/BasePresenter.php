<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\Model\EmployeeAuthenticator;
use Nette;
use App\Model\Parameters;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	use \Nextras\Application\UI\SecuredLinksPresenterTrait;

	/**
	 * @var Parameters $parameters
	 */
	protected $parameters;

	public function injectParameters(Parameters $parameters)
	{
		$this->parameters = $parameters->getParam();
	}

	/**
	 * @var EmployeeAuthenticator $employeeAuthenticator
	 */
	private $employeeAuthenticator;

	public function injectEmployeeAuthenticator(EmployeeAuthenticator $employeeAuthenticator)
	{
		$this->employeeAuthenticator = $employeeAuthenticator;
	}

	protected function startup(): void
	{
		parent::startup();

		$this->user->getStorage()->setNamespace('Admin');
		$this->user->setAuthenticator($this->employeeAuthenticator);

		if ($this->name != "Admin:Sign" AND !$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		} elseif ($this->user->isLoggedIn() AND !$this->user->isInRole('spravce') AND !$this->user->isInRole('prodejce')) {
			$this->redirect(':Front:Sign:out');
		}
	}

	protected function createComponentNavbar(): Navbar
	{
		$items = [
			'Objednávky' => ['Orders:default'],
			'Zákazníci' => ['Customers:default'],
			'Dodavatelé' => ['Suppliers:default'],
		];
		if ($this->user->isInRole('spravce')) {
			$items['Zaměstnanci'] = ['Employees:default'];
		}
		$items['Odhlásit se'] = ['Sign:out'];

		$control = new Navbar('Administrace', $items);
		return $control;
	}
}
