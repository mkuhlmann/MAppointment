<?php declare(strict_types=1);

namespace App\Db;

use App\Application;
use App\Config;
use ParagonIE\EasyDB\EasyDB;

class DatabaseManager {
	private Application $app;
	private EasyDB $db;

	/**
	 * 
	 * @param \App\Config $config
	 * @param \App\Application $app
	 */
	public function __construct(Config $config, Application $app)
	{
		$this->app = $app;
		$this->db = \ParagonIE\EasyDB\Factory::fromArray([
			'mysql:host=captain.fra02.mkuhlmann.org;port=42306;dbname=appointments',
			'root',
			'jmFKGdu6dRb3o7zbep'
		]);
	}

	public function getDb() {
		return $this->db;
	}

	public function migrate() {
		/** @var \App\Db\Migrations\MigrationRunner $migrationRunner */
		$migrationRunner = $this->app->get(\App\Db\Migrations\MigrationRunner::class);
		$migrationRunner->migrate();
	}
}

