<?php

namespace PhpRepos\Logger\Media;

use Closure;
use PhpRepos\Logger\Log\Level;
use PhpRepos\Logger\Log\Message;
use function PhpRepos\Logger\Logs\log;

/**
 * Returns a closure for logging messages to the system log.
 *
 * This function creates a closure that logs messages to the system log using
 * the `syslog` function, mapping the log level to the appropriate syslog priority.
 *
 * @return Closure A closure that accepts a Message object and logs it to the system log.
 *
 * @example
 * $logger = system_log();
 * $logger(Message::info("Application started", ["app" => "MyApp"]));
 * // Logs an INFO level message to the system log.
 */
function system_log(): Closure
{
    return function (Message $log) {
        $priority = match ($log->level) {
            Level::ALERT => LOG_ALERT,
            Level::CRITICAL => LOG_CRIT,
            Level::DEBUG => LOG_DEBUG,
            Level::EMERGENCY => LOG_EMERG,
            Level::ERROR => LOG_ERR,
            Level::INFO => LOG_INFO,
            Level::NOTICE => LOG_NOTICE,
            Level::WARNING => LOG_WARNING,
        };

        syslog($priority, json_encode($log->jsonSerialize()));
    };
}

/**
 * Returns a closure for logging messages to a file without locking.
 *
 * This function creates a closure that appends log messages in JSON format to
 * the specified file path. It does not use file locking, so it may not be
 * suitable for concurrent writes.
 *
 * @param string $path The file path where log messages will be written.
 * @return Closure A closure that accepts a Message object and writes it to the specified file.
 *
 * @example
 * $logger = file_put('/var/log/myapp.log');
 * $logger(Message::error("Database connection failed", ["db" => "main"]));
 * // Appends an ERROR level message to /var/log/myapp.log.
 */
function file_put(string $path): Closure
{
    return function (Message $log) use ($path) {
        file_put_contents($path, json_encode($log->jsonSerialize()) . PHP_EOL, FILE_APPEND);
    };
}

/**
 * Returns a closure for logging messages to a file with exclusive locking.
 *
 * This function creates a closure that appends log messages in JSON format to
 * the specified file path, using file locking to ensure safe concurrent writes.
 * If the file cannot be opened, locked, or the message cannot be encoded, it
 * logs a critical message using the default logger.
 *
 * @param string $path The file path where log messages will be written.
 * @return Closure A closure that accepts a Message object and writes it to the specified file with locking.
 *
 * @example
 * $logger = file_lock('/var/log/myapp.log');
 * $logger(Message::warning("High memory usage", ["usage" => "85%"]));
 * // Appends a WARNING level message to /var/log/myapp.log with file locking.
 */
function file_lock(string $path): Closure
{
    return function (Message $log) use ($path) {
        $fp = fopen($path, 'a');
        if ($fp === false) {
            log(Message::critical('Failed to open log file', ['file' => $path]));
            return;
        }
        if (flock($fp, LOCK_EX)) {
            $json = json_encode($log);
            if ($json !== false) {
                fwrite($fp, $json . PHP_EOL);
            } else {
                $message = Message::critical('Failed to encode log to JSON');
                log($message);
                fwrite($fp, json_encode($message) . PHP_EOL);
            }
            flock($fp, LOCK_UN);
        } else {
            log(Message::critical('Failed to acquire lock for log file', ['file' => $path]));
        }
        fclose($fp);
    };
}
