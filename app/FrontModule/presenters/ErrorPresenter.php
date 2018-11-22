<?php
declare(strict_types=1);

namespace App\FrontModule\Presenters;

use Nette;
use Nette\Application\IResponse;
use Nette\Application\Request;
use Tracy\ILogger;


class ErrorPresenter implements Nette\Application\IPresenter
{
	use Nette\SmartObject;

	/**
	 * @var ILogger $logger
	 */
	private $logger;

	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * @param Request $request
	 * @return IResponse
	 */
	public function run(Request $request): Iresponse
	{
		$e = $request->getParameter('exception');

		if ($e instanceof Nette\Application\BadRequestException) {
			return new Nette\Application\Responses\ForwardResponse($request->setPresenterName('Front:Error4xx'));
		}

		$this->logger->log($e, ILogger::EXCEPTION);
		return new Nette\Application\Responses\CallbackResponse(function () {
			require __DIR__ . '/templates/Error/500.phtml';
		});
	}
}
