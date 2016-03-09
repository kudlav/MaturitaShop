<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
    protected function startup()
    {
        parent::startup();
        $this->template->page = $this->getName();

        $this->template->name = $this->getUser()->getIdentity()->name;
        $this->template->surname = $this->getUser()->getIdentity()->surname;
        $this->template->cartCount = 1;
        $this->template->cartPrice = 3600;

    }

    protected function createComponentSearch()
    {
        $form = new Form();

        $form->addText('searchTerm')
            ->setType('search')
            ->setAttribute('placeholder', 'Hledaný produkt...');

        $form->addSubmit('send','Hledat')
            ->setAttribute('class', 'button');

        $form->onSuccess[] = array($this, 'postFormSucceeded');

        return $form;
    }

}
