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
