<?php

namespace App\Presenters;

use Nette;
use App\Model;


class ProductPresenter extends BasePresenter
{

    public function renderShow()
    {
        $this->template->product = array();
        $this->template->product['name'] = 'Použité levé světlo';
        $this->template->product['description'] = 'Prodám Učebnici českého jazyka pro střední školy - komunikace a sloh. Učebnice je nepoužitá, nepopsaná, bez známek poškození. Učebnici jsem ochotný předat kdekoliv po Brně, po dohodě ji zaslat poštou. V případě zájmu jsem schopný sehnat další kusy. (Český jazyk pro SŠ – Komunikace a sloh, nakladatelství: Fraus, 2009 (1. vydání), počet stran: 152).';
        $this->template->product['condition'] = 'Použité';
        $this->template->product['price'] = 12300;
        $this->template->product['quantity'] = 14;
        $this->template->product['category'] = 'Ostatní';
        $this->template->product['id'] = 'A00013';
    }


}
