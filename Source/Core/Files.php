<?php

namespace PhpRepos\Logger\Core\Files;

use PhpRepos\Logger\Core\Data\Message;
use PhpRepos\Logger\Core\Exceptions\JsonEncodeException;
use PhpRepos\Logger\Core\Exceptions\FileLockException;
use PhpRepos\Logger\Core\Exceptions\FileWriteException;
use PhpRepos\Logger\Core\Exceptions\FileException;
use function PhpRepos\Logger\Platform\Filesystem\mkdir_recursive;
use function PhpRepos\Logger\Platform\Filesystem\dirname;
use function PhpRepos\Logger\Platform\Filesystem\open;
use function PhpRepos\Logger\Platform\Filesystem\lock;
use function PhpRepos\Logger\Platform\Filesystem\write;
use function PhpRepos\Logger\Platform\Filesystem\close;
use function PhpRepos\Logger\Platform\Filesystem\append;
use function PhpRepos\Logger\Platform\Filesystem\exists;
use function PhpRepos\Logger\Platform\Filesystem\is_directory;
use function PhpRepos\Logger\Platform\Filesystem\is_writable_directory;
use function PhpRepos\Logger\Platform\Filesystem\mkdir;
use function PhpRepos\Logger\Core\Messages\validate;
use function PhpRepos\Logger\Core\Messages\encode;

/**
 * Ensures a file and its parent directory exist and are writable.
 *
 * Validates that the parent directory of the given file path exists and is writable.
 * If the parent directory doesn't exist, it will be created. If the file doesn't
 * exist, it will be created as an empty file. This ensures the path is ready for
 * writing without requiring file creation on first write.
 *
 * @param string $path The file path to validate and prepare.
 * @return true Always returns true on success.
 * @throws FileException If parent is not a directory, cannot create directory,
 *                       directory is not writable, or cannot create file.
 */
function ensure_exists(string $path): true
{
    $dir = dirname($path);

    // Check if parent is a file (not a directory)
    if (exists($dir) && !is_directory($dir)) {
        throw new FileException('Parent path is not a directory: ' . $dir);
    }

    // If directory doesn't exist, try to create it
    if (!exists($dir)) {
        if (!mkdir($dir)) {
            throw new FileException('Cannot create directory: ' . $dir);
        }
    }

    // Check if directory is writable
    if (!is_writable_directory($dir)) {
        throw new FileException('Parent directory is not writable: ' . $dir);
    }

    // Create the file if it doesn't exist
    if (!file_exists($path)) {
        if (@file_put_contents($path, '') === false) {
            throw new FileException('Cannot create file: ' . $path);
        }
    }

    return true;
}

/**
 * Orchestrates file locking and writing with comprehensive error handling.
 *
 * This function implements the full workflow for safe concurrent file writes:
 * 1. Validates the message can be JSON encoded
 * 2. Opens file and acquires exclusive lock
 * 3. Encodes the message to JSON
 * 4. Writes to the file
 * 5. Releases the lock and closes the file
 *
 * Ensures proper cleanup (release lock, close file) even if errors occur.
 *
 * @param string $path File path to write to.
 * @param Message $message Message to write.
 * @return bool True on success.
 * @throws JsonEncodeException If the message cannot be JSON encoded.
 * @throws FileLockException If the file cannot be opened or locked.
 * @throws FileWriteException If writing to the file fails.
 *
 * @example
 * try {
 *     lock_and_write('/var/log/app.log', Message::info('Request completed'));
 * } catch (FileLockException $e) {
 *     // Handle locking error
 * }
 */
function lock_and_write(string $path, Message $message): bool
{
    if (!validate($message)) {
        throw new JsonEncodeException(
            'Message cannot be JSON encoded',
            $message
        );
    }

    $handler = open($path, 'a');
    if (!$handler) {
        throw new FileLockException(
            'Failed to open log file',
            $path
        );
    }

    if (!lock($handler, LOCK_EX)) {
        close($handler);
        throw new FileLockException(
            'Failed to acquire lock for log file',
            $path
        );
    }

    try {
        $json = encode($message);
        $write_result = write($handler, $json . PHP_EOL);

        if (!$write_result) {
            throw new FileWriteException(
                'Failed to write to log file',
                $path
            );
        }

        lock($handler, LOCK_UN);
        close($handler);

        return $write_result;
    } catch (\Exception $e) {
        lock($handler, LOCK_UN);
        close($handler);
        throw $e;
    }
}

/**
 * Orchestrates simple file writing without locking.
 *
 * This is a simpler alternative to lock_and_write() for scenarios where
 * file locking is not required (single-process logging, low-concurrency).
 * Simply validates, encodes, and appends to the file.
 *
 * @param string $path File path to write to.
 * @param Message $message Message to write.
 * @return bool True on success.
 * @throws JsonEncodeException If the message cannot be JSON encoded.
 * @throws FileWriteException If writing to the file fails.
 *
 * @example
 * put_in_file('/tmp/debug.log', Message::debug('Debug info'));
 */
function put_in_file(string $path, Message $message): bool
{
    // Validate JSON encoding
    if (!validate($message)) {
        throw new JsonEncodeException(
            'Message cannot be JSON encoded',
            $message
        );
    }

    // Encode to JSON
    $json = encode($message);

    // Append to file
    $result = append($path, $json . PHP_EOL);

    if (!$result) {
        throw new FileWriteException(
            'Failed to write to log file',
            $path
        );
    }

    return $result;
}
