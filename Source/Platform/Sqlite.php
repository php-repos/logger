<?php

namespace PhpRepos\Logger\Platform\Sqlite;

use PDO;
use PDOException;

/**
 * Opens or creates a SQLite database connection using PDO.
 *
 * @param string $path Path to the SQLite database file or ':memory:' for in-memory database.
 * @return PDO|false Database connection or false on failure.
 */
function open(string $path): PDO|false
{
    try {
        $dsn = 'sqlite:' . $path;
        return new PDO($dsn);
    } catch (\Throwable) {
        return false;
    }
}

/**
 * Executes a SQL statement.
 *
 * @param PDO $db Database connection.
 * @param string $sql SQL statement to execute.
 * @return bool True on success, false on failure.
 */
function exec(PDO $db, string $sql): bool
{
    try {
        return $db->exec($sql) !== false;
    } catch (PDOException) {
        return false;
    }
}

/**
 * Inserts a row into a table using a prepared statement.
 *
 * Builds an INSERT statement dynamically from the data array keys and binds
 * all values as text. This is a generic function that doesn't know about
 * any specific table schema.
 *
 * @param PDO $db Database connection.
 * @param string $table Table name to insert into.
 * @param array $data Associative array of field => value to insert.
 * @return bool True on success, false on failure.
 *
 * @example
 * insert($db, 'users', ['id' => '123', 'name' => 'John', 'email' => 'john@example.com']);
 */
function insert(PDO $db, string $table, array $data): bool
{
    try {
        $fields = array_keys($data);
        $placeholders = array_map(fn($field) => ':' . $field, $fields);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $stmt = $db->prepare($sql);

        if (!$stmt) {
            return false;
        }

        foreach ($data as $field => $value) {
            $stmt->bindValue(':' . $field, $value, PDO::PARAM_STR);
        }

        return $stmt->execute();
    } catch (PDOException) {
        return false;
    }
}

