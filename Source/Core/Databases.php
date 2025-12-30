<?php

namespace PhpRepos\Logger\Core\Databases;

use PhpRepos\Logger\Core\Data\Message;
use PhpRepos\Logger\Core\Exceptions\SqliteException;
use function PhpRepos\Logger\Platform\Sqlite\open;
use function PhpRepos\Logger\Platform\Sqlite\exec;
use function PhpRepos\Logger\Platform\Sqlite\insert;
use function PhpRepos\Logger\Platform\Jsons\encode;

/**
 * Creates the logs table in SQLite database if it doesn't exist.
 *
 * This function knows the database schema and creates the table structure.
 * Safe to call multiple times - uses CREATE TABLE IF NOT EXISTS.
 *
 * @param string $path Path to SQLite database file or ':memory:' for in-memory.
 * @param string $table_name Name of the table to create.
 * @return bool True on success.
 * @throws SqliteException If database operations fail.
 *
 * @example
 * if (sqlite_create_table('/var/log/app.db', 'logs')) {
 *     // Table is ready
 * }
 */
function sqlite_create_table(string $path, string $table_name): bool
{
    $db = open($path);
    if (!$db) {
        throw new SqliteException(
            'Failed to open SQLite database',
            $path
        );
    }

    try {
        // Core layer knows the schema
        $schema = sprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                id TEXT PRIMARY KEY,
                level TEXT NOT NULL,
                message TEXT NOT NULL,
                context JSON,
                time TEXT NOT NULL
            )',
            $table_name
        );

        if (!exec($db, $schema)) {
            throw new SqliteException(
                'Failed to create logs table',
                $path
            );
        }

        return true;
    } catch (\Exception $e) {
        throw $e;
    }
}

/**
 * Writes a log message to SQLite database.
 *
 * This function assumes the table already exists. Call sqlite_create_table()
 * first to ensure the table is ready.
 *
 * @param string $path Path to SQLite database file or ':memory:' for in-memory.
 * @param string $table_name Name of the table to write to.
 * @param Message $message Message to write.
 * @return bool True on success.
 * @throws SqliteException If database operations fail.
 *
 * @example
 * try {
 *     sqlite_write('/var/log/app.db', 'logs', Message::create('INFO', 'Request completed'));
 * } catch (SqliteException $e) {
 *     // Handle database error
 * }
 */
function sqlite_write(string $path, string $table_name, Message $message): bool
{
    // Open database connection
    $db = open($path);
    if (!$db) {
        throw new SqliteException(
            'Failed to open SQLite database',
            $path
        );
    }

    try {
        $serialized = $message->jsonSerialize();
        $context_json = encode($serialized['context']);

        $data = [
            'id' => $serialized['id'],
            'level' => $serialized['level'],
            'message' => $serialized['message'],
            'context' => $context_json,
            'time' => $serialized['time'],
        ];

        $result = insert($db, $table_name, $data);

        if (!$result) {
            throw new SqliteException(
                'Failed to insert log entry',
                $path
            );
        }

        return true;
    } catch (\Exception $e) {
        throw $e;
    }
}
