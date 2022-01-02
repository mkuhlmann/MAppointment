<?php

declare(strict_types=1);

namespace App\Http;

use Laminas\Diactoros\Response\JsonResponse;

class JsonNumericAwareResponse extends JsonResponse
{
	/**
	 * Default flags for json_encode; value of:
	 *
	 * <code>
	 * JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK
	 * </code>
	 *
	 * @const int
	 */
	const DEFAULT_JSON_FLAGS = parent::DEFAULT_JSON_FLAGS | JSON_NUMERIC_CHECK;

	/**
	 * Create a JSON response with the given data.
	 *
	 * Default JSON encoding is performed with the following options, which
	 * produces RFC4627-compliant JSON, capable of embedding into HTML.
	 *
	 * - JSON_HEX_TAG
	 * - JSON_HEX_APOS
	 * - JSON_HEX_AMP
	 * - JSON_HEX_QUOT
	 * - JSON_UNESCAPED_SLASHES
	 *
	 * @param mixed $data Data to convert to JSON.
	 * @param int $status Integer status code for the response; 200 by default.
	 * @param array $headers Array of headers to use at initialization.
	 * @param int $encodingOptions JSON encoding options to use.
	 * @throws Exception\InvalidArgumentException if unable to encode the $data to JSON.
	 */
	public function __construct(
		$data,
		int $status = 200,
		array $headers = [],
		int $encodingOptions = self::DEFAULT_JSON_FLAGS
	) {
		parent::__construct($data, $status, $headers, $encodingOptions);
	}
}
