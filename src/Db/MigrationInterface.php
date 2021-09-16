<?php declare(strict_types=1);

namespace App\Db;

interface MigrationInterface {
	public function up() : void;
	public function down() : void;
}