<?php

namespace App\AdminModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Application\UI\Presenter;


class ContactFormFactory extends Nette\Object
{

	/**
	 * @var Presenter $presenter
	 * @var string $email
	 */
	private $presenter, $email;

	public function __construct(Presenter $presenter, $email)
	{
		$this->email = $email;
		$this->presenter = $presenter;
	}

	/**
	 * @return Form
	 */
	public function create()
	{
		$form = new Form;

		$form->addText('subject','Předmět:')
			->setRequired('Zadejte předmět emailu');

		$form->addHidden('email')
			->setRequired('Načtěte stránku znovu');

		$form->addTextArea('message','Text:')
			->setRequired('Zadejte text emailu');

		$form->addSubmit('send','Poslat email');

		$form->onSuccess[] = array($this, 'formSucceeded');
		return $form;
	}

	public function formSucceeded(Form $form, $values)
	{
		$mail = new Message();
		$mail->setFrom('Auto CVK <info@auto-cvk.cz>')
			->addTo($values->email)
			->setSubject($values->subject)
			->setBody($values->message);

		$mailer = new SendmailMailer();
		$mailer->send($mail);

		$this->presenter->flashMessage('Emailová zpráva byla odeslána');
		$this->presenter->redirect($this->presenter->getAction(TRUE),$this->presenter->getParameter('id'));
	}
}
