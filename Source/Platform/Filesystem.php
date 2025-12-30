<?php

namespace PhpRepos\Logger\Platform\Filesystem;

/**
 * Creates a directory.
 *
 * @param string $path Directory path to create.
 * @param int $mode Permissions mode (default: 0755).
 * @param bool $recursive Create parent directories if needed (default: true).
 * @return bool True on success, false on failure.
 */
function mkdir_recursive(string $path, int $mode = 0755, bool $recursive = true): bool
{
    if (is_dir($path)) {
        return true;
    }

    return mkdir($path, $mode, $recursive);
}

/**
 * Opens a file in the specified mode.
 *
 * @param string $path File path to open.
 * @param string $mode File mode ('a' for append, 'r' for read, 'w' for write, etc.).
 * @return resource|false File handle on success, false on failure.
 */
function open(string $path, string $mode)
{
    return fopen($path, $mode);
}

/**
 * Acquires a lock on a file handle.
 *
 * @param resource $handler File handle.
 * @param int $operation Lock type (LOCK_SH, LOCK_EX, LOCK_UN).
 * @return bool True on success, false on failure.
 */
function lock($handler, int $operation): bool
{
    return flock($handler, $operation);
}

/**
 * Writes content to an open file handle.
 *
 * Writes the provided content to the file handle obtained from lock_handler().
 *
 * @param resource $handler File handle.
 * @param string $content Content to write.
 * @return bool True on success, false on failure.
 *
 * @example
 * $handler = lock_handler('/var/log/app.log');
 * write($handler, "Log message\n");
 */
function write($handler, string $content): bool
{
    return fwrite($handler, $content) !== false;
}

/**
 * Gets the directory part of a path.
 *
 * @param string $path File path.
 * @return string Directory path.
 */
function dirname(string $path): string
{
    return \dirname($path);
}

/**
 * Closes a file handle.
 *
 * Closes the file handle and frees associated resources.
 *
 * @param resource $handler File handle.
 * @return bool True on success, false on failure.
 *
 * @example
 * close($handler);
 */
function close($handler): bool
{
    return fclose($handler);
}

/**
 * Appends content to a file without locking.
 *
 * Simple append operation without any locking mechanism. Suitable for
 * single-process scenarios or when locking is not required.
 *
 * @param string $path File path.
 * @param string $content Content to append.
 * @return bool True on success, false on failure.
 *
 * @example
 * append('/var/log/app.log', "Log message\n");
 */
function append(string $path, string $content): bool
{
    return file_put_contents($path, $content, FILE_APPEND) !== false;
}

/**
 * Checks if a file or directory path exists.
 *
 * @param string $path The path to check.
 * @return bool True if path exists (file or directory).
 */
function exists(string $path): bool
{
    return file_exists($path);
}

/**
 * Checks if a path is a directory.
 *
 * @param string $path The path to check.
 * @return bool True if path exists and is a directory.
 */
function is_directory(string $path): bool
{
    return is_dir($path);
}

/**
 * Checks if a directory is writable.
 *
 * @param string $path The directory path to check.
 * @return bool True if path is a directory and is writable.
 */
function is_writable_directory(string $path): bool
{
    return is_dir($path) && is_writable($path);
}

/**
 * Creates a directory.
 *
 * @param string $path The directory path to create.
 * @param int $permissions The directory permissions (default: 0777).
 * @param bool $recursive Whether to create parent directories (default: true).
 * @return bool True on success, false on failure.
 */
function mkdir(string $path, int $permissions = 0777, bool $recursive = true): bool
{
    return @\mkdir($path, $permissions, $recursive);
}
