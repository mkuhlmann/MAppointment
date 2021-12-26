<?php declare(strict_types=1);

namespace App\Db\Migrations;

use App\Db\MigrationManager;
use League\Container\Container;

class MigrationRunner {

	private $availableMigrations = [
		Migration_20211916_Initial::class
	];

	private $db;
	private $container;

	public function __construct(\ParagonIE\EasyDB\EasyDB $db, Container $container)
	{
		$this->db = $db;
		$this->container = $container;
	}

	public function migrate() {
		$manager = new MigrationManager($this, $this->db->getPdo(), $this->availableMigrations);
		$manager->migrate();
	}

	public function run($migrationClass) {
		$migration = new $migrationClass($this->db);
		$migration->up();
	}
}
