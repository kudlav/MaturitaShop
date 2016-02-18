<?php

namespace App\Presenters;

use Nette;
use App\Model;


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


}
