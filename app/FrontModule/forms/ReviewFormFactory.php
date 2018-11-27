<?php
declare(strict_types=1);

namespace App\FrontModule\Forms;

use App\Model\ProductManager;
use Nette;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Utils\ArrayHash;


class ReviewFormFactory
{
	use Nette\SmartObject;

	/**
	 * @var Presenter $presenter
	 * @var ProductManager $productManager
	 */
	private $presenter, $productManager;

	public function __construct(Presenter $presenter, ProductManager $productManager)
	{
		$this->presenter = $presenter;
		$this->productManager = $productManager;
	}

	public function createForm(): Form
	{
		$form = new Form;

		$form->addRadioList('rating','Počet hvězdiček: *', ['☆☆☆☆☆','★☆☆☆☆','★★☆☆☆','★★★☆☆','★★★★☆','★★★★★'])
			->setRequired('Zadejte hodnocení');

		$form->addTextArea('pros','Klady:')
			->setAttribute('class','pros frm-long form-input')
			->setMaxLength(255);

		$form->addTextArea('cons','Zápory:')
			->setAttribute('class','cons frm-long form-input')
			->setMaxLength(255);

		$form->addTextArea('summary','Shrnutí:')
			->setAttribute('class','frm-long form-input')
			->setMaxLength(255);

		$form->addSubmit('submit', 'Odeslat hodnocení')
			->setAttribute('class','form-button button');

		$form->onSuccess[] = [$this, 'formSucceeded'];

		$productId = $this->presenter->getParameter('id');
		$review = $this->productManager->getItemReviewByUser($productId, $this->presenter->user->id)->fetch();

		if ($review != false) {
			$form->getComponent('rating')->setValue($review->pocet_hvezdicek);
			$form->getComponent('pros')->setValue($review->klady);
			$form->getComponent('cons')->setValue($review->zapory);
			$form->getComponent('summary')->setValue($review->shrnuti);
		}

		return $form;
	}

	/**
	 * @param Form $form
	 * @param ArrayHash $values
	 * @return bool
	 * @throws Nette\Application\AbortException
	 */
	public function formSucceeded(Form $form, ArrayHash $values): bool
	{
		$product = $this->productManager->getItem($this->presenter->getParameter('id'));
		$result =  $this->productManager->setItemReviewByUser($product->katalogove_cislo, $this->presenter->user->id, (int) $values['rating'], $values['pros'], $values['cons'], $values['summary']);

		$this->presenter->flashMessage('Děkujeme za Vaši recenzi.');
		$this->presenter->redirect('Product:default', $product->katalogove_cislo);

		return $result;
	}
}
