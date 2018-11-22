<?php
declare(strict_types=1);

namespace App\Model;

use Nette\Database\Table\IRow;


class DisabledItemException extends \Exception
{
	/**
	 * @var string $message
	 */
	protected $message;

	/**
	 * DisabledItemException constructor.
	 * @param IRow $product
	 */
	public function __construct(Irow $product)
	{
		$this->message = "Produkt $product->katalogove_cislo ($product->nazev) již nelze objednat, odeberte jej.";
	}
}
