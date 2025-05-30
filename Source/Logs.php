<?php

namespace PhpRepos\Logger\Logs;

use PhpRepos\Logger\Log\Message;
use function PhpRepos\Logger\Media\system_log;

/**
 * Manages default logging media.
 *
 * This function allows setting or retrieving the default logging media. If no
 * media are provided, it returns the current default media, initializing with
 * the system log if none are set.
 *
 * @param callable ...$media Optional logging media callables to set as defaults.
 * @return array An array of default logging media callables.
 *
 * @example
 * // Get default media (initializes with system_log if not set)
 * $media = default_media();
 * // Set custom default media
 * $customMedia = file_put('/var/log/myapp.log');
 * default_media($customMedia);
 */
function default_media(callable ...$media): array
{
    static $default_media = [];

    if (!empty($media)) {
        $default_media = $media;
    }

    if (empty($default_media)) {
        $default_media = [system_log()];
    }

    return $default_media;
}

/**
 * Logs a message to the specified or default media.
 *
 * This function logs a message by passing it to each of the provided media
 * callables. If no media are provided, it uses the default media configured
 * via default_media().
 *
 * @param Message $message The log message to be processed.
 * @param callable ...$media Optional logging media callables to use for this log.
 * @return void
 *
 * @example
 * $message = Message::info("Application started", ["app" => "MyApp"]);
 * log($message); // Logs to default media (e.g., system log)
 * log($message, file_put('/var/log/myapp.log')); // Logs to specified file
 */
function log(Message $message, callable ...$media): void
{
    $media = empty($media) ? default_media() : $media;

    foreach ($media as $medium) {
        $medium($message);
    }
}
