<?php declare(strict_types=1);

namespace App\Db\Migrations;

use App\Db\MigrationInterface;
use ParagonIE\EasyDB\EasyDB;

class Migration_20211916_Initial implements MigrationInterface {
	private EasyDB $db;

	public function __construct(EasyDB $db)
	{
		$this->db = $db;	
	}

	public function up(): void
	{
		$this->db->exec(
			'CREATE TABLE appointments(
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`name` varchar(255) NOT NULL,
				`description` text
			);
			
			CREATE TABLE appointment_slots(
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`slots` int NOT NULL,
				`free` int NOT NULL,
				`date` datetime NOT NULL,
				`duration` int NOT NULL,
				`created_at` datetime,
				`updated_at` datetime,
				`appointment_id` varchar(21) NOT NULL REFERENCES appointments (`id`)
			);
			
			CREATE TABLE appointment_slot_bookings(
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`secret` varchar(32) NOT NULL,
				`firstname` varchar(255),
				`lastname` varchar(255),
				`email` varchar(255),
				`created_at` datetime,
				`updated_at` datetime,
				`appointment_slot_id` varchar(21) REFERENCES appointment_slots (`id`)
			);
			
			CREATE TABLE users(
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`username` varchar(64) UNIQUE NOT NULL,
				`email` varchar(64) UNIQUE NOT NULL,
				`password` varchar(255) NOT NULL
			);
		');
	}

	public function down(): void
	{

	}
}