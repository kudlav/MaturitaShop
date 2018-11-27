<?php
declare(strict_types=1);

namespace App\AdminModule\Forms;

use Nette;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SendmailMailer;
use Nette\Application\UI\Presenter;
use Nette\Utils\ArrayHash;


class ContactFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var Presenter $presenter
	 * @var string $email_from
	 */
	private $presenter, $email_from;

	public function __construct(Presenter $presenter, $email_from)
	{
		$this->email_from = $email_from;
		$this->presenter = $presenter;
	}

	/**
	 * @return Form
	 */
	public function create(): Form
	{
		$form = new Form;

		$form->addText('subject','Předmět:')
			->setRequired('Zadejte předmět emailu');

		$form->addHidden('email')
			->setRequired('Načtěte stránku znovu');

		$form->addTextArea('message','Text:')
			->setRequired('Zadejte text emailu');

		$form->addSubmit('send','Poslat email');

		$form->onSuccess[] = [$this, 'formSucceeded'];
		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 * @throws Nette\Application\AbortException
	 */
	public function formSucceeded(Form $form, ArrayHash $values): void
	{
		$mail = new Message();
		$mail->setFrom('IIShop <'.$this->email_from.'>')
			->addTo($values->email)
			->setSubject($values->subject)
			->setBody($values->message);

		$mailer = new SendmailMailer();
		$mailer->send($mail);

		$this->presenter->flashMessage('Emailová zpráva byla odeslána');
		$this->presenter->redirect($this->presenter->getAction(true), $this->presenter->getParameter('id'));
	}
}
