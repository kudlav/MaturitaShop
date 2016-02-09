<?php
use Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

$router = new RouteList;
$router[] = new Route('kontakty', 'Info:kontakty');
$router[] = new Route('obchodni-podminky', 'Info:podminky');
$router[] = new Route('doprava-platba', 'Info:nakup');
$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

$container->addService('router', $router);

return $container;
