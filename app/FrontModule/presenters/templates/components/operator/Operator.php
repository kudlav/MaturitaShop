<?php

namespace App\FrontModule\Presenters;

use Nette\Application\UI\Control;


class Operator extends Control
{
	/**
	 * @var array $contact
	 */
	private $contact;

	public function  __construct($contact)
	{
		parent::__construct();

		$this->contact = $contact;
	}

	public function render()
	{
		$template = $this->template;
		$template->setFile(__DIR__ . '/operator.latte');
		$template->name = $this->contact['name'];
		$template->phone = $this->contact['phone'];
		$template->email = $this->contact['email'];

		$template->render();
	}
}
