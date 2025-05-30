<?php

namespace PhpRepos\Logger\Log;

/**
 * Defines log levels for the logging system.
 *
 * This enum represents the various severity levels that can be used when logging
 * messages, based on common logging standards (e.g., RFC 5424). Each case
 * corresponds to a specific log level with an associated string value.
 */
enum Level: string
{
    /**
     * System is unusable and requires immediate attention.
     */
    case ALERT = 'ALERT';

    /**
     * Critical conditions that need urgent action.
     */
    case CRITICAL = 'CRITICAL';

    /**
     * Detailed information for debugging purposes.
     */
    case DEBUG = 'DEBUG';

    /**
     * Emergency condition, system-wide failure.
     */
    case EMERGENCY = 'EMERGENCY';

    /**
     * Error conditions that indicate a failure in the system.
     */
    case ERROR = 'ERROR';

    /**
     * Informational messages about normal system operations.
     */
    case INFO = 'INFO';

    /**
     * Conditions that are noteworthy but not critical.
     */
    case NOTICE = 'NOTICE';

    /**
     * Warning conditions that may indicate potential issues.
     */
    case WARNING = 'WARNING';
}
