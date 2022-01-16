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
			'CREATE TABLE organizations (
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`name` varchar(255) NOT NULL,
				`createdAt` datetime,
				`updatedAt` datetime
			);

			CREATE TABLE appointments (
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`organizationId` varchar(21) NOT NULL REFERENCES organizations (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				`name` varchar(255) NOT NULL,
				`isActive` tinyint(1) NOT NULL DEFAULT 0,
				`description` text,
				`mailText` text,
				`location` varchar(128),
				`latitude` decimal(10,8),
				`longitude` decimal(11,8),
				`cancellationDeadline` int,
				`requireMailValidation` tinyint(1) NOT NULL DEFAULT 0,
				`requirePhoneNumber` tinyint(1) NOT NULL DEFAULT 0,
				`canComment` tinyint(1) NOT NULL DEFAULT 1,
				`createdAt` datetime,
				`updatedAt` datetime
			);
			
			CREATE TABLE slots (
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`appointmentId` varchar(21) NOT NULL REFERENCES appointments (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				`slots` int NOT NULL,
				`free` int NOT NULL,
				`start` datetime NOT NULL,
				`end` datetime NOT NULL,
				`createdAt` datetime,
				`updatedAt` datetime
			);
			
			CREATE TABLE bookings (
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`slotId` varchar(21) REFERENCES slots (`id`),
				`secret` varchar(32) NOT NULL,
				`firstName` varchar(255),
				`lastName` varchar(255),
				`email` varchar(255),
				`comment` text,
				`mailSentAt` datetime,
				`mailConfirmedAt` datetime,
				`createdAt` datetime,
				`updatedAt` datetime				
			);

			CREATE TABLE users (
				`id` varchar(21) UNIQUE NOT NULL PRIMARY KEY,
				`organizationId` varchar(21) NOT NULL REFERENCES organizations (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				`isSuperAdmin` tinyint(1) NOT NULL DEFAULT 0,
				`username` varchar(64) UNIQUE NOT NULL,
				`email` varchar(64) UNIQUE NOT NULL,
				`password` varchar(255) NOT NULL,
				`createdAt` datetime,
				`updatedAt` datetime	
			);
			
		');
	}

	public function down(): void
	{

	}
}