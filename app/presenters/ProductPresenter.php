<?php

namespace App\Presenters;

use Nette;
use App\Model;


class ProductPresenter extends BasePresenter
{

	public function renderShow()
	{
		$this->template->product = [
			'name' => 'Použité levé světlo',
			'description' => 'Prodám Učebnici českého jazyka pro střední školy - komunikace a sloh. Učebnice je nepoužitá, nepopsaná, bez známek poškození. Učebnici jsem ochotný předat kdekoliv po Brně, po dohodě ji zaslat poštou. V případě zájmu jsem schopný sehnat další kusy. (Český jazyk pro SŠ – Komunikace a sloh, nakladatelství: Fraus, 2009 (1. vydání), počet stran: 152).',
			'condition' => 'Použité',
			'price' => 12300,
			'quantity' => 14,
			'category' => 'Ostatní',
			'id' => 'A00013',
		];
	}


}
