<?php

namespace PhpRepos\Logger\Platform\System;

/**
 * Writes to system log (syslog).
 *
 * Writes a message to the system log using the syslog function
 * with the specified priority level.
 *
 * @param int $priority Syslog priority (LOG_EMERG, LOG_ALERT, LOG_CRIT, etc.).
 * @param string $message The message to log.
 * @return bool True on success, false on failure.
 *
 * @example
 * write(LOG_ERR, '{"level":"ERROR","message":"Database error"}');
 */
function write(int $priority, string $message): bool
{
    if (!function_exists('syslog')) {
        // Fallback: write to stderr
        error_log('[SYSLOG] ' . $message);
        return true;
    }

    return syslog($priority, $message);
}
