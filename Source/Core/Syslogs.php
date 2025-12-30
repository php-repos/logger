<?php

namespace PhpRepos\Logger\Core\Syslogs;

use PhpRepos\Logger\Core\Data\Message;
use PhpRepos\Logger\Core\Exceptions\JsonEncodeException;
use function PhpRepos\Logger\Core\Messages\encode;
use function PhpRepos\Logger\Core\Messages\validate;
use function PhpRepos\Logger\Platform\System\write as system_write;

/**
 * Orchestrates writing to system log with level mapping.
 *
 * Writes a log message to the system log (syslog), mapping the logger's
 * Level enum to the appropriate syslog priority constant. Handles JSON
 * encoding and validates the message before writing.
 *
 * @param Message $message Message to log to syslog.
 * @return bool True on success.
 * @throws JsonEncodeException If the message cannot be JSON encoded.
 *
 * @example
 * $message = Message::error('Database connection failed');
 * write($message); // Logs to syslog with LOG_ERR priority
 */
function write(Message $message): bool
{
    if (!validate($message)) {
        throw new JsonEncodeException(
            'Message cannot be JSON encoded for syslog',
            $message
        );
    }

    $priority = map_level_to_priority($message->level);
    $json = encode($message);

    return system_write($priority, $json);
}

/**
 * Maps logger level string to syslog priority constant.
 *
 * Converts the logger's level string to the corresponding syslog priority
 * constant (LOG_EMERG, LOG_ALERT, LOG_CRIT, etc.).
 *
 * @param string $level The log level string (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG).
 * @return int The corresponding syslog priority.
 *
 * @example
 * map_level_to_priority('ERROR'); // Returns LOG_ERR
 * map_level_to_priority('DEBUG'); // Returns LOG_DEBUG
 */
function map_level_to_priority(string $level): int
{
    return match (strtoupper($level)) {
        'EMERGENCY' => LOG_EMERG,
        'ALERT' => LOG_ALERT,
        'CRITICAL' => LOG_CRIT,
        'ERROR' => LOG_ERR,
        'WARNING' => LOG_WARNING,
        'NOTICE' => LOG_NOTICE,
        'INFO' => LOG_INFO,
        'DEBUG' => LOG_DEBUG,
        default => LOG_INFO,
    };
}
