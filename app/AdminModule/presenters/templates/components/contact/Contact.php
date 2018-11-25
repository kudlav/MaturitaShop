<?php
declare(strict_types=1);

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\ContactFormFactory;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Database\IRow;


class Contact extends Control
{

	/**
	 * @var array $order
	 * @var string $email_from
	 */
	private $order, $email_from;

	public function  __construct(array $order, string $email_from)
	{
		parent::__construct();

		$this->order = $order;
		$this->email_from = $email_from;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/contact.latte');
		$template->detail = $this->order;

		$template->render();
	}

	public function createComponentContactForm(): Form
	{
		$form = new ContactFormFactory($this->getPresenter(), $this->email_from);
		return $form->create();
	}
}
