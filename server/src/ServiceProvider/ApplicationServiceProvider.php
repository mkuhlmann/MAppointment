<?php

declare(strict_types=1);

namespace App\ServiceProvider;

use App\Application;
use App\Db\DatabaseManager;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ApplicationServiceProvider extends AbstractServiceProvider
{

	public function provides(string $id): bool
	{
		return $id == 'app' || $id == Application::class;
	}

	public function register(): void
	{
		$container = $this->getContainer();

		$app = new \App\Application($container->get('baseDir'), $container);

		$container
			->add(\App\Application::class, $app)
			->addTag('app')
			->setShared(true);

		$container
			->add(DatabaseManager::class)
			->addArguments([$app])
			->addTag('dbManager')
			->setShared(true);

		$container
			->add(\ParagonIE\EasyDB\EasyDB::class, $app->get(DatabaseManager::class)->getDb())
			->addTag('db')
			->setShared(true);
	}
}
