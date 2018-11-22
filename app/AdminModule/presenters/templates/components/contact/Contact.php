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
	 * @var array $details
	 * @var string $email_from
	 */
	private $details, $email_from;

	public function  __construct(IRow $details, string $email_from)
	{
		parent::__construct();

		$this->details = $details;
		$this->email_from = $email_from;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/contact.latte');
		$template->detail = $this->details;

		$template->render();
	}

	public function createComponentContactForm(): Form
	{
		$form = new ContactFormFactory($this->getPresenter(), $this->email_from);
		return $form->create();
	}
}
