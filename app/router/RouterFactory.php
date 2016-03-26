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
		$router[] = new Route('kontakty', 'Info:kontakty');
		$router[] = new Route('obchodni-podminky', 'Info:podminky');
		$router[] = new Route('doprava-platba', 'Info:nakup');
		$router[] = new Route('product/<id>', 'Product:default');
		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		return $router;
	}

}
