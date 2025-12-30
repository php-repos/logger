<?php

namespace PhpRepos\Logger\API\Media;

use Closure;
use PhpRepos\Logger\Core\Data\Message;
use PhpRepos\Logger\Core\Exceptions\FileException;
use function PhpRepos\Logger\Core\Syslogs\write as syslog_write;
use function PhpRepos\Logger\Core\Files\lock_and_write;
use function PhpRepos\Logger\Core\Files\put_in_file;
use function PhpRepos\Logger\Core\Files\ensure_exists;
use function PhpRepos\Logger\Core\Databases\sqlite_create_table;
use function PhpRepos\Logger\Core\Databases\sqlite_write;
use function PhpRepos\Logger\Core\Caches\once;

/**
 * Returns a closure for logging messages to the system log.
 *
 * Creates a media closure that logs messages to the system log (syslog),
 * automatically mapping log levels to syslog priorities. If syslog is not
 * available, falls back to error_log().
 *
 * @return Closure A closure that accepts a Message and logs to syslog.
 *
 * @example
 * use function PhpRepos\Logger\API\Media\system_log;
 * use function PhpRepos\Logger\API\Config\set_default_media;
 *
 * set_default_media(system_log());
 * // All logs will go to system log
 */
function system_log(): Closure
{
    return function (Message $log) {
        try {
            syslog_write($log);
        } catch (\Exception $e) {
            // Fallback: If syslog fails, log the error to stderr
            // This prevents losing the original log message
            error_log('[LOGGER] Failed to write to syslog: ' . $e->getMessage());
            error_log('[LOGGER] Original message: ' . $log->text);
        }
    };
}

/**
 * Returns a closure for logging messages to a file without locking.
 *
 * Creates a media closure that appends log messages to the specified file
 * without any locking mechanism. Suitable for single-process scenarios or
 * when locking overhead is not acceptable.
 *
 * Note: In concurrent environments, use file_lock() instead to prevent
 * race conditions and corrupted log files.
 *
 * Path validation occurs once when the media is first created, ensuring
 * the directory exists and is writable before any log writes occur.
 *
 * If writing fails, the error is logged to stderr to prevent losing the
 * original log message.
 *
 * @param string $path The file path where log messages will be written.
 * @return Closure A closure that accepts a Message and writes to file.
 * @throws FileException If the path validation fails (parent not writable, etc.).
 *
 * @example
 * use function PhpRepos\Logger\API\Media\file_put;
 * use function PhpRepos\Logger\API\Logs\info;
 *
 * info("Request completed", ["duration" => "150ms"], file_put('/tmp/debug.log'));
 */
function file_put(string $path): Closure
{
    // Validate path is writable once on first call
    $cache_key = 'logger_file_put_validated_' . $path;

    once($cache_key, fn () => ensure_exists($path));

    return function (Message $log) use ($path) {
        try {
            put_in_file($path, $log);
        } catch (\Exception $e) {
            // Fallback: Log error using stderr to avoid infinite recursion
            error_log('[LOGGER] Failed to write to file ' . $path . ': ' . $e->getMessage());
            error_log('[LOGGER] Original message: ' . $log->text);
        }
    };
}

/**
 * Returns a closure for logging messages to a file with exclusive locking.
 *
 * Creates a media closure that appends log messages to the specified file
 * using exclusive file locking (flock with LOCK_EX). This ensures safe
 * concurrent writes in multi-process/multi-threaded environments.
 *
 * The locking mechanism prevents race conditions and ensures log integrity.
 * Path validation occurs once when the media is first created, ensuring
 * the directory exists and is writable before any log writes occur.
 *
 * If locking or writing fails, the error is logged to stderr to prevent
 * losing the original log message.
 *
 * @param string $path The file path where log messages will be written.
 * @return Closure A closure that accepts a Message and writes with locking.
 * @throws FileException If the path validation fails (parent not writable, etc.).
 *
 * @example
 * use function PhpRepos\Logger\API\Media\file_lock;
 * use function PhpRepos\Logger\API\Config\set_default_media;
 *
 * set_default_media(file_lock('/var/log/app.log'));
 * // All logs will be safely written with file locking
 */
function file_lock(string $path): Closure
{
    // Validate path is writable once on first call
    $cache_key = 'logger_file_lock_validated_' . $path;

    once($cache_key, fn () => ensure_exists($path));

    return function (Message $log) use ($path) {
        try {
            lock_and_write($path, $log);
        } catch (\Exception $e) {
            // Fallback: Log error using stderr to avoid infinite recursion
            error_log('[LOGGER] Failed to write to file ' . $path . ': ' . $e->getMessage());
            error_log('[LOGGER] Original message: ' . $log->text);
        }
    };
}

/**
 * Returns a closure for logging messages to a SQLite database.
 *
 * Creates a media closure that writes log messages to a SQLite database.
 * The database and table are created automatically when this function is called
 * (not on first log write), so any configuration errors are caught immediately.
 *
 * Performance optimization: The table existence check happens ONCE when the
 * media is configured (e.g., set_default_media(sqlite(...))), not on every log
 * write. This makes subsequent writes very fast.
 *
 * Each log entry is stored with its id, level, message, context (as JSON),
 * and timestamp.
 *
 * @param string $path Database file path.
 * @param string|null $table_name Optional name of the table to use (default: 'logs').
 * @return Closure A closure that accepts a Message and writes to database.
 *
 * @example
 * use function PhpRepos\Logger\API\Media\sqlite;
 * use function PhpRepos\Logger\API\Config\set_default_media;
 *
 * // Default table name 'logs' (table_name is optional)
 * set_default_media(sqlite('/var/log/app.db'));
 *
 * // Custom table name
 * set_default_media(sqlite('/var/log/app.db', 'application_logs'));
 */
function sqlite(string $path, ?string $table_name = null): Closure
{
    // Use default table name if not specified
    $table_name = $table_name ?? 'logs';

    // Create table once and cache the result
    $cache_key = 'logger_sqlite_table_status' . $path . '@' . $table_name;

    once($cache_key, function () use ($path, $table_name) {
        sqlite_create_table($path, $table_name);
        return true;
    });

    // Return closure that writes to database
    return function (Message $log) use ($path, $table_name) {
        try {
            sqlite_write($path, $table_name, $log);
        } catch (\Exception $e) {
            // Fallback: Log error using stderr to avoid infinite recursion
            error_log('[LOGGER] Failed to write to database ' . $path . ': ' . $e->getMessage());
            error_log('[LOGGER] Original message: ' . $log->text);
        }
    };
}
