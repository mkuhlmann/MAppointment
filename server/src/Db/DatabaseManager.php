<?php declare(strict_types=1);

namespace App\Db;

use App\Application;
use ParagonIE\EasyDB\EasyDB;

class DatabaseManager {
	private Application $app;
	private EasyDB $db;

	/**
	 * 
	 * @param \App\Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->db = \ParagonIE\EasyDB\Factory::fromArray([
			$_ENV['DB_DSN'],
			$_ENV['DB_USER'],
			$_ENV['DB_PASSWORD']
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

