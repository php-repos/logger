<?php

namespace PhpRepos\Logger\API\Logs;

use function PhpRepos\Logger\API\Config\get_default_media;
use function PhpRepos\Logger\Core\Messages\create as create_message;

/**
 * Logs a message to the specified or default media.
 *
 * This is the main logging function that sends a message to one or more
 * media outputs. If no media are provided, uses the default media configured
 * via set_default_media().
 *
 * @param string $text The log message content.
 * @param string $level The severity level (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG).
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * use function PhpRepos\Logger\API\Logs\log;
 * use function PhpRepos\Logger\API\Media\file_lock;
 *
 * log("Application started", "INFO", ["app" => "MyApp"]);
 * // Logs to default media (system log)
 *
 * log("Connection failed", "ERROR", [], file_lock('/var/log/app.log'));
 * // Logs to specified file
 */
function log(string $text, string $level, array $context = [], callable ...$media): void
{
    $message = create_message($level, $text, $context);
    $media = empty($media) ? get_default_media() : $media;

    foreach ($media as $medium) {
        $medium($message);
    }
}

/**
 * Creates and logs an emergency level message in one call.
 *
 * Emergency messages indicate system-wide failures that require immediate attention.
 *
 * @param string $text The log message content.
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * emergency("Database cluster is down", ["cluster_id" => "prod-01"]);
 */
function emergency(string $text, array $context = [], callable ...$media): void
{
    log($text, 'EMERGENCY', $context, ...$media);
}

/**
 * Creates and logs an alert level message in one call.
 *
 * Alert messages indicate conditions requiring immediate attention.
 *
 * @param string $text The log message content.
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * alert("System overload detected", ["cpu" => "95%", "memory" => "98%"]);
 */
function alert(string $text, array $context = [], callable ...$media): void
{
    log($text, 'ALERT', $context, ...$media);
}

/**
 * Creates and logs a critical level message in one call.
 *
 * Critical messages indicate serious conditions that need urgent action.
 *
 * @param string $text The log message content.
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * critical("Payment gateway unavailable", ["gateway" => "stripe"]);
 */
function critical(string $text, array $context = [], callable ...$media): void
{
    log($text, 'CRITICAL', $context, ...$media);
}

/**
 * Creates and logs an error level message in one call.
 *
 * Error messages indicate failures in the system that should be investigated.
 *
 * @param string $text The log message content.
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * error("Failed to send email", ["to" => "user@example.com", "error" => "SMTP timeout"]);
 */
function error(string $text, array $context = [], callable ...$media): void
{
    log($text, 'ERROR', $context, ...$media);
}

/**
 * Creates and logs a warning level message in one call.
 *
 * Warning messages indicate potential issues that may require attention.
 *
 * @param string $text The log message content.
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * warning("Disk usage above 80%", ["disk" => "/dev/sda1", "usage" => "85%"]);
 */
function warning(string $text, array $context = [], callable ...$media): void
{
    log($text, 'WARNING', $context, ...$media);
}

/**
 * Creates and logs a notice level message in one call.
 *
 * Notice messages indicate noteworthy conditions that are not critical.
 *
 * @param string $text The log message content.
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * notice("User password changed", ["user_id" => 123]);
 */
function notice(string $text, array $context = [], callable ...$media): void
{
    log($text, 'NOTICE', $context, ...$media);
}

/**
 * Creates and logs an info level message in one call.
 *
 * Info messages provide informational details about normal system operations.
 *
 * @param string $text The log message content.
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * info("User logged in", ["user_id" => 123, "ip" => "192.168.1.1"]);
 */
function info(string $text, array $context = [], callable ...$media): void
{
    log($text, 'INFO', $context, ...$media);
}

/**
 * Creates and logs a debug level message in one call.
 *
 * Debug messages provide detailed information for debugging purposes.
 *
 * @param string $text The log message content.
 * @param array $context Optional contextual data (default is empty array).
 * @param callable ...$media Optional logging media callables.
 * @return void
 *
 * @example
 * debug("Query executed", ["sql" => "SELECT * FROM users", "duration" => "15ms"]);
 */
function debug(string $text, array $context = [], callable ...$media): void
{
    log($text, 'DEBUG', $context, ...$media);
}
