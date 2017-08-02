<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\ContactFormFactory;
use Nette\Application\UI\Control;


class Contact extends Control
{

	/**
	 * @var array $details
	 * @var string $email_from
	 */
	private $details, $email_from;

	public function  __construct($details, $email_from)
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

	public function createComponentContactForm()
	{
		$form = new ContactFormFactory($this->getPresenter(), $this->email_from);
		return $form->create();
	}
}
