<?php

namespace PhpRepos\Logger\Core\Exceptions;

use Exception;

/**
 * Exception thrown when file locking fails.
 *
 * This exception is raised when the logger cannot acquire an exclusive lock
 * on a log file, typically due to permissions issues, filesystem problems,
 * or the file being locked by another process.
 */
class FileLockException extends Exception
{
    /**
     * Constructs a new FileLockException.
     *
     * @param string $message The exception message (default: 'Failed to acquire lock for log file').
     * @param string|null $filePath The path to the file that couldn't be locked (stored for debugging).
     * @param int $code The exception code (default: 0).
     * @param Exception|null $previous The previous exception for chaining.
     */
    public function __construct(
        string $message = 'Failed to acquire lock for log file',
        public readonly ?string $filePath = null,
        int $code = 0,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
