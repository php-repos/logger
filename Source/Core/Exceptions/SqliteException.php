<?php

namespace PhpRepos\Logger\Core\Exceptions;

use Exception;

/**
 * Exception thrown when SQLite operations fail.
 *
 * Provides context about the database path that failed.
 */
class SqliteException extends Exception
{
    /**
     * @param string $message Exception message.
     * @param string $path Database path that caused the error.
     */
    public function __construct(
        string $message,
        public readonly string $path
    ) {
        parent::__construct($message);
    }
}
