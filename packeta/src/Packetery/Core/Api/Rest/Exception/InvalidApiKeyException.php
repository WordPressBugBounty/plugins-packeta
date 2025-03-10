<?php

namespace Packetery\Core\Api\Rest\Exception;

class InvalidApiKeyException extends \RuntimeException {
	public function __construct( string $message ) {
		parent::__construct( $message );
	}

	public static function createFromMissingKey(): self {
		return new self( 'API key is missing' );
	}
}
