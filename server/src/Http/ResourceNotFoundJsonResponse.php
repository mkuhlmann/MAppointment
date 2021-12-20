<?php

declare(strict_types=1);

namespace App\Http;

use Laminas\Diactoros\Response\JsonResponse;

class ResourceNotFoundJsonResponse extends JsonResponse
{
	public function __construct(
		$data = ['error' => 'resource not found'],
		int $status = 404,
		array $headers = []
	) {
		parent::__construct($data, $status, $headers);
	}
}
