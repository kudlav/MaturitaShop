<?php

namespace App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;


class RouterFactory
{

	/**
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter()
	{
		$router = new RouteList;
		$router[] = new Route('kontakty', 'Front:Info:kontakty');
		$router[] = new Route('obchodni-podminky', 'Front:Info:podminky');
		$router[] = new Route('doprava-platba', 'Front:Info:nakup');

		$router[] = new Route('product/<id [0-9]+>', 'Front:Product:default');

		$router[] = new Route('<presenter>/<action>[/<id>]', array(
			'module' => 'Front',
			'presenter' => 'Homepage',
			'action' => 'default',
		));
		return $router;
	}

}
