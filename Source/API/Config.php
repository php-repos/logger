<?php

namespace PhpRepos\Logger\API\Config;

use function PhpRepos\Logger\API\Media\system_log;
use function PhpRepos\Logger\Core\Caches\set;
use function PhpRepos\Logger\Core\Caches\get;

/**
 * Sets the default logging media.
 *
 * Configures the default media that will be used when log() is called
 * without explicit media arguments. Returns the array of set media.
 *
 * @param callable ...$media Logging media callables to set as defaults.
 * @return array The configured default media.
 *
 * @example
 * use function PhpRepos\Logger\API\Media\file_lock;
 * use function PhpRepos\Logger\API\Config\set_default_media;
 *
 * set_default_media(file_lock('/var/log/app.log'));
 * // All subsequent log() calls without media will write to this file
 */
function set_default_media(callable ...$media): array
{
    return set('logger_default_media', $media);
}

/**
 * Gets the current default logging media.
 *
 * Returns the currently configured default media. If no media has been
 * set, initializes with system_log() as the default.
 *
 * @return array Current default logging media.
 *
 * @example
 * use function PhpRepos\Logger\API\Config\get_default_media;
 *
 * $media = get_default_media();
 * // Returns [system_log()] if no custom media has been set
 */
function get_default_media(): array
{
    $cached = get('logger_default_media');

    if ($cached !== null && !empty($cached)) {
        return $cached;
    }

    // Initialize with system_log() if not set
    return set('logger_default_media', [system_log()]);
}
