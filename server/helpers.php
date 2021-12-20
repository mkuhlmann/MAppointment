<?php declare(strict_types=1);

function app() : \App\Application {
	return \App\Application::getInstance();
}

function dbdate() : string {
	return gmdate('Y-m-d H:i:s');
}