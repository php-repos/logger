<?php

namespace PhpRepos\Logger\Core\Exceptions;

use Exception;

/**
 * Exception thrown when file writing fails.
 *
 * This exception is raised when the logger cannot write to a log file,
 * typically due to disk full, permissions issues, or I/O errors.
 */
class FileWriteException extends Exception
{
    /**
     * Constructs a new FileWriteException.
     *
     * @param string $message The exception message (default: 'Failed to write to log file').
     * @param string|null $filePath The path to the file that couldn't be written (stored for debugging).
     * @param int $code The exception code (default: 0).
     * @param Exception|null $previous The previous exception for chaining.
     */
    public function __construct(
        string $message = 'Failed to write to log file',
        public readonly ?string $filePath = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
