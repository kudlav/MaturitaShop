<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette;
use App\Model\ProductManager;


class HomepagePresenter extends BasePresenter
{
	/**
	 * @var ProductManager $productManager
	 */
	private $productManager;

	public function __construct(ProductManager $productManager)
	{
		parent::__construct();

		$this->productManager = $productManager;
	}

	public function renderDefault(string $search = "", string $cat = ""): void
	{
		if ($search != "") {
			$this->template->products = $this->productManager->searchProduct($search);
			$this->template->title = "Hledaný výraz: $search";
		}
		elseif ($cat != "") {
			$this->template->products = $this->productManager->getProducts((string) $cat);
			$this->template->title = "Kategorie: $cat";
		}
		else {
			$this->template->products = $this->productManager->getProducts();
			$this->template->title = 'Nové produkty';
		}
	}
}
