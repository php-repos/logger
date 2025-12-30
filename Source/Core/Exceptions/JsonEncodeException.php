<?php

namespace PhpRepos\Logger\Core\Exceptions;

use Exception;

/**
 * Exception thrown when JSON encoding fails.
 *
 * This exception is raised when a log message cannot be encoded to JSON,
 * typically due to circular references, non-UTF8 data, or other encoding issues.
 * It stores the original data and JSON error message for debugging.
 */
class JsonEncodeException extends Exception
{
    /**
     * Constructs a new JsonEncodeException.
     *
     * @param string $message The exception message (default: 'Failed to encode message to JSON').
     * @param mixed $data The data that failed to encode (stored for debugging).
     * @param string|null $jsonError The JSON error message from json_last_error_msg().
     * @param int $code The exception code (default: 0).
     * @param Exception|null $previous The previous exception for chaining.
     */
    public function __construct(
        string $message = 'Failed to encode message to JSON',
        public readonly mixed $data = null,
        public readonly ?string $jsonError = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
