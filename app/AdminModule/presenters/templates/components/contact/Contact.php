<?php

namespace App\AdminModule\Presenters;

use App\AdminModule\Forms\ContactFormFactory;
use Nette\Application\UI\Control;


class Contact extends Control
{

	private $details;

	public function  __construct($details)
	{
		parent::__construct();

		$this->details = $details;
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
		$form = new ContactFormFactory($this->getPresenter(), $this->details['username']);
		return $form->create();
	}
}
