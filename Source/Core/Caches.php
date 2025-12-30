<?php

namespace PhpRepos\Logger\Core\Caches;

/**
 * Internal function to access shared storage.
 *
 * @return array Reference to the static storage array.
 */
function &storage(): array
{
    static $storage = [];
    return $storage;
}

/**
 * Stores a value in cache.
 *
 * @param string $key The cache key.
 * @param mixed $value The value to store.
 * @return mixed The stored value.
 *
 * @example
 * set('default_media', [$media1, $media2]); // Returns [$media1, $media2]
 */
function set(string $key, mixed $value): mixed
{
    $storage = &storage();
    $storage[$key] = $value;
    return $value;
}

/**
 * Retrieves a value from cache with optional default.
 *
 * @param string $key The cache key.
 * @param mixed $default The default value if key doesn't exist.
 * @return mixed The cached value or default.
 *
 * @example
 * $media = get('default_media', []); // Returns [] if not found
 */
function get(string $key, mixed $default = null): mixed
{
    $storage = &storage();
    return $storage[$key] ?? $default;
}

/**
 * Checks if a cache key exists.
 *
 * @param string $key The cache key to check.
 * @return bool True if the key exists, false otherwise.
 *
 * @example
 * if (has('default_media')) {
 *     $media = get('default_media');
 * }
 */
function has(string $key): bool
{
    $storage = &storage();
    return isset($storage[$key]) || array_key_exists($key, $storage);
}

/**
 * Executes a closure once and caches the result.
 *
 * If the key already exists in cache, returns true immediately without
 * executing the closure. Otherwise, executes the closure, caches the
 * result, and returns true.
 *
 * @param string $key The cache key.
 * @param callable $callback The closure to execute if key doesn't exist.
 * @return bool Always returns true.
 *
 * @example
 * once('table_created', function () {
 *     create_database_table();
 *     return true;
 * });
 * // Second call won't execute the closure
 * once('table_created', function () {
 *     // This won't run
 * });
 */
function once(string $key, callable $callback): bool
{
    if (has($key)) {
        return true;
    }

    $result = $callback();
    set($key, $result);
    return true;
}
