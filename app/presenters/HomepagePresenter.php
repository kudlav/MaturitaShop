<?php

namespace App\Presenters;

use Nette;
use App\Model;


class HomepagePresenter extends BasePresenter
{
    protected function createComponentNavbar()
    {
        $control = new Navbar();
        return $control;
    }
}
