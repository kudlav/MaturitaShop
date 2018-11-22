<?php
declare(strict_types=1);

namespace App\Model;

use Nette;


/**
 * Provides access to parameters section of config.neon file
 */
class Parameters
{
	use Nette\SmartObject;

	/**
	 * @var array $parameters
	 */
	private $parameters;

	public function __construct(array $parameters)
	{
		$this->parameters = $parameters;
	}

	public function getParam(): array
	{
		return $this->parameters;
	}
}
