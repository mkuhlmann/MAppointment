<?php
declare(strict_types=1);

require 'bootstrap.php';
/** @var $container Container */

$app = $container->get(\App\Application::class);

if (php_sapi_name() == 'cli') {
	/** @var \ParagonIE\EasyDB\EasyDB $db */

	$db = $app->get('db');
	switch($argv[1] ?? null) {
		case 'migrate':
			echo 'Migrating ...'.PHP_EOL;
			$app->get('dbManager')->migrate();
			break;
		case 'reset':			
			echo 'Dropping tables ...'.PHP_EOL;
			$db->exec('DROP TABLE IF EXISTS bookings; DROP TABLE IF EXISTS slots; DROP TABLE IF EXISTS appointments; DROP TABLE IF EXISTS users;DROP TABLE IF EXISTS organizations; DELETE FROM _migrations;');
			echo 'Migrating ...'.PHP_EOL;
			$app->get('dbManager')->migrate();
			break;
		default:
			echo 'No command specified.'.PHP_EOL;
			echo 'Usage: php cli.php [migrate|reset]'.PHP_EOL;
			echo 'migrate: Migrates the database'.PHP_EOL;
			echo 'reset: Drops all tables and migrates the database'.PHP_EOL;
			break;
	}


}