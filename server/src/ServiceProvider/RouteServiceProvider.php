<?php


namespace App\ServiceProvider;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Route\Router;
use League\Route\Strategy\ApplicationStrategy;

class RouteServiceProvider extends AbstractServiceProvider {

    public function provides(string $id): bool {		
		return $id == 'router' || $id == Router::class;

	 }

    public function register(): void {		
		$container = $this->getContainer();

		$routerStrategy = new ApplicationStrategy();
		$routerStrategy->setContainer($container);

		$router = new Router();
		$router->setStrategy($routerStrategy);

		extract([
			'router' => $router,
			'container' => $container,
		]);
		include $container->get('baseDir') . '/routes.php';

		$container
			->add(Router::class, $router)
			->addTag('router')
			->setShared(true);
	 }

	
}