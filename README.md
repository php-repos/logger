# Logger Package

A lightweight, powerful logging package for PHP applications built with Natural Architecture. This package provides PSR-3 compatible logging to multiple destinations (system log, files, SQLite database) with a clean, functional API.

## Features

- **PSR-3 Compatible Log Levels**: Eight standard severity levels (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG)
- **Multiple Media Types**: Log to system log, files (with/without locking), or SQLite database
- **Structured Logging**: JSON serialization with context data
- **Thread-Safe**: File locking support for concurrent writes
- **Flexible Configuration**: Set default media or specify per-log
- **Natural Architecture**: Clean three-layer design (API/Core/Platform) for maintainability
- **Lightweight**: Minimal dependencies, function-based design
- **Designed for phpkg**: Seamlessly integrates with the phpkg package manager

## Installation

### Requirements

- PHP 8.3 or higher
- [phpkg](https://phpkg.com) package manager
- PDO SQLite extension (for SQLite media)

### Via phpkg

```bash
phpkg add logger
```

## Quick Start

```php
use function PhpRepos\Logger\API\Logs\info;
use function PhpRepos\Logger\API\Logs\error;

// Simple logging with default media (system log)
info('Application started');
error('Database connection failed', ['host' => 'localhost', 'port' => 5432]);
```

## Usage

### Basic Logging

The simplest way to log is using the convenience functions:

```php
use function PhpRepos\Logger\API\Logs\{emergency, alert, critical, error, warning, notice, info, debug};

emergency('System is unusable', ['reason' => 'Out of memory']);
alert('Action must be taken immediately', ['service' => 'payment-processor']);
critical('Critical condition', ['cpu' => '98%']);
error('Runtime error', ['file' => 'app.php', 'line' => 42]);
warning('Warning condition', ['disk_space' => '85%']);
notice('Normal but significant', ['user_login' => 'admin']);
info('Informational message', ['request_id' => 'abc123']);
debug('Debug-level message', ['query' => 'SELECT * FROM users']);
```

Each convenience function accepts:
- `string $text` - The log message
- `array $context = []` - Optional context data (will be JSON encoded)
- `callable ...$media` - Optional media (uses default if not specified)

### Logging to Specific Media

You can override the default media for individual logs:

```php
use function PhpRepos\Logger\API\Logs\info;
use function PhpRepos\Logger\API\Media\file_lock;

// Log to a specific file with locking
info('User registered', ['email' => 'user@example.com'], file_lock('/var/log/app.log'));
```

### Logging to Multiple Media

Log to multiple destinations simultaneously:

```php
use function PhpRepos\Logger\API\Logs\critical;
use function PhpRepos\Logger\API\Media\{system_log, file_lock, sqlite};

critical(
    'Payment processing failed',
    ['order_id' => '12345', 'amount' => 99.99],
    system_log(),
    file_lock('/var/log/payments.log'),
    sqlite('/var/log/payments.db')
);
```

### Using the Generic log() Function

All convenience functions use the generic `log()` function internally:

```php
use function PhpRepos\Logger\API\Logs\log;
use function PhpRepos\Logger\API\Media\file_put;

log('Custom severity message', 'CUSTOM_LEVEL', ['key' => 'value'], file_put('/tmp/debug.log'));
```

## Configuration

### Setting Default Media

Configure which media are used when none are specified:

```php
use function PhpRepos\Logger\API\Config\set_default_media;
use function PhpRepos\Logger\API\Media\{file_lock, sqlite};

// Set default media for all subsequent logs
set_default_media(
    file_lock('/var/log/app.log'),
    sqlite('/var/log/app.db')
);

// Now this logs to both file and database
info('Application started');
```

### Getting Current Default Media

```php
use function PhpRepos\Logger\API\Config\get_default_media;

$current = get_default_media();
// Returns array of media closures (system_log() by default)
```

## Media Types

### System Log

Logs to the system log (syslog) with automatic priority mapping:

```php
use function PhpRepos\Logger\API\Media\system_log;
use function PhpRepos\Logger\API\Config\set_default_media;

set_default_media(system_log());
```

**Priority Mapping:**
- EMERGENCY → LOG_EMERG
- ALERT → LOG_ALERT
- CRITICAL → LOG_CRIT
- ERROR → LOG_ERR
- WARNING → LOG_WARNING
- NOTICE → LOG_NOTICE
- INFO → LOG_INFO
- DEBUG → LOG_DEBUG

### File (No Locking)

Fast file logging without locking. Suitable for single-process scenarios:

```php
use function PhpRepos\Logger\API\Media\file_put;
use function PhpRepos\Logger\API\Logs\info;

info('Quick log entry', [], file_put('/tmp/debug.log'));
```

**Warning:** Not safe for concurrent writes from multiple processes.

### File (With Locking)

Thread-safe file logging with exclusive locking (flock with LOCK_EX):

```php
use function PhpRepos\Logger\API\Media\file_lock;
use function PhpRepos\Logger\API\Config\set_default_media;

set_default_media(file_lock('/var/log/app.log'));
```

**Recommended** for production environments with multiple processes.

### SQLite Database

Structured logging to SQLite database. Ideal for queryable logs:

```php
use function PhpRepos\Logger\API\Media\sqlite;
use function PhpRepos\Logger\API\Logs\error;

error('Database query failed', ['query' => 'SELECT ...'], sqlite('/var/log/app.db'));
```

**Database Schema:**
```sql
CREATE TABLE logs (
    id TEXT PRIMARY KEY,        -- UUID v4
    level TEXT NOT NULL,         -- EMERGENCY, ALERT, etc.
    message TEXT NOT NULL,       -- Log message text
    context JSON,                -- JSON context data (queryable!)
    time TEXT NOT NULL           -- ISO 8601 timestamp (UTC)
);
```

**Basic Querying:**
```php
$db = new PDO('sqlite:/var/log/app.db');

// Get recent errors
$result = $db->query("SELECT * FROM logs WHERE level = 'ERROR' ORDER BY time DESC LIMIT 10");
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['time']} [{$row['level']}] {$row['message']}\n";
    $context = json_decode($row['context'], true);
    print_r($context);
}
```

**Advanced JSON Querying:**

The `context` column is stored as JSON, enabling powerful queries using SQLite's JSON functions:

```php
$db = new PDO('sqlite:/var/log/app.db');

// Find logs for a specific user
$stmt = $db->prepare("SELECT * FROM logs WHERE json_extract(context, '$.user_id') = :user_id");
$stmt->bindValue(':user_id', 123, PDO::PARAM_INT);
$result = $stmt->execute();

// Find logs with specific error code
$result = $db->query("
    SELECT * FROM logs
    WHERE json_extract(context, '$.error_code') = 500
    ORDER BY time DESC
");

// Find logs where a nested property exists
$result = $db->query("
    SELECT * FROM logs
    WHERE json_extract(context, '$.metadata.ip') IS NOT NULL
");

// Count logs by user
$result = $db->query("
    SELECT
        json_extract(context, '$.user_id') as user_id,
        COUNT(*) as log_count
    FROM logs
    WHERE json_extract(context, '$.user_id') IS NOT NULL
    GROUP BY user_id
    ORDER BY log_count DESC
");

// Find logs with arrays in context
$result = $db->query("
    SELECT * FROM logs
    WHERE json_type(context, '$.tags') = 'array'
    AND json_array_length(context, '$.tags') > 0
");
```

**Why JSON Type?**
- **Queryable**: Use `json_extract()`, `json_each()`, `json_array_length()` and other SQLite JSON functions
- **Type Safety**: Column explicitly indicates JSON data
- **Performance**: Same storage as TEXT, but enables indexed JSON queries
- **Developer Experience**: No need to decode JSON in PHP for simple queries

## Log Message Structure

Every log message is internally represented as a `Message` object with:

- `id` (string) - Unique identifier (UUID v4)
- `level` (string) - Log level (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG)
- `text` (string) - The log message
- `context` (array) - Associative array of context data
- `time` (DateTimeImmutable) - Timestamp in UTC

### JSON Format

Log messages are JSON serializable:

```json
{
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "level": "ERROR",
    "message": "Database connection failed",
    "context": {
        "host": "localhost",
        "port": 5432,
        "retry_count": 3
    },
    "time": "2025-12-30T10:30:45.123456+00:00"
}
```

## API Reference

### Logging Functions (PhpRepos\Logger\API\Logs)

#### log()
```php
log(string $text, string $level, array $context = [], callable ...$media): void
```
Generic logging function. All other logging functions use this internally.

**Parameters:**
- `$text` - Log message text
- `$level` - Severity level (string)
- `$context` - Context data (associative array)
- `...$media` - Media closures (uses default if empty)

#### emergency(), alert(), critical(), error(), warning(), notice(), info(), debug()
```php
emergency(string $text, array $context = [], callable ...$media): void
alert(string $text, array $context = [], callable ...$media): void
critical(string $text, array $context = [], callable ...$media): void
error(string $text, array $context = [], callable ...$media): void
warning(string $text, array $context = [], callable ...$media): void
notice(string $text, array $context = [], callable ...$media): void
info(string $text, array $context = [], callable ...$media): void
debug(string $text, array $context = [], callable ...$media): void
```
Convenience functions for each log level.

**Parameters:**
- `$text` - Log message text
- `$context` - Context data (associative array)
- `...$media` - Media closures (uses default if empty)

### Configuration Functions (PhpRepos\Logger\API\Config)

#### set_default_media()
```php
set_default_media(callable ...$media): array
```
Sets the default media used when no media are specified in log calls.

**Returns:** Array of set media

#### get_default_media()
```php
get_default_media(): array
```
Gets the current default media. Returns `[system_log()]` if no default is set.

**Returns:** Array of media closures

### Media Functions (PhpRepos\Logger\API\Media)

#### system_log()
```php
system_log(): Closure
```
Returns a closure that logs to system log (syslog).

#### file_put()
```php
file_put(string $path): Closure
```
Returns a closure that logs to a file without locking.

**Parameters:**
- `$path` - File path for logs

#### file_lock()
```php
file_lock(string $path): Closure
```
Returns a closure that logs to a file with exclusive locking.

**Parameters:**
- `$path` - File path for logs

#### sqlite()
```php
sqlite(string $path, ?string $table_name = null): Closure
```
Returns a closure that logs to a SQLite database.

**Parameters:**
- `$path` - Database file path
- `$table_name` - Optional table name (default: 'logs')

## Error Handling

All media include fallback error handling. If a log write fails, the error is logged to stderr to prevent losing the original message:

```
[LOGGER] Failed to write to file /var/log/app.log: Permission denied
[LOGGER] Original message: Database connection failed
```

This ensures you're always aware of logging failures without crashing your application.

## Examples

### Production Setup

```php
use function PhpRepos\Logger\API\Config\set_default_media;
use function PhpRepos\Logger\API\Media\{file_lock, sqlite};
use function PhpRepos\Logger\API\Logs\{info, error, critical};

// Configure logging once at application startup
set_default_media(
    file_lock('/var/log/myapp.log'),
    sqlite('/var/log/myapp.db')
);

// Use throughout your application
info('Application started', ['version' => '2.0.0']);

try {
    // Application logic
    processPayment($order);
} catch (PaymentException $e) {
    error('Payment processing failed', [
        'order_id' => $order->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
```

### Development Setup

```php
use function PhpRepos\Logger\API\Config\set_default_media;
use function PhpRepos\Logger\API\Media\file_put;
use function PhpRepos\Logger\API\Logs\debug;

// Fast logging to file without locking for development
set_default_media(file_put('/tmp/dev.log'));

debug('Variable dump', ['user' => $user, 'request' => $_REQUEST]);
```

### Temporary Override

```php
use function PhpRepos\Logger\API\Logs\critical;
use function PhpRepos\Logger\API\Media\{system_log, file_lock};

// Use default media for most logs
info('Normal operation');

// Override media for critical issues
critical(
    'Database server down',
    ['server' => 'db-primary', 'last_ping' => time()],
    system_log(),           // Alert sysadmin via syslog
    file_lock('/var/log/critical.log')  // Also write to dedicated file
);
```

## Performance Considerations

- **file_put()**: Fastest, no locking overhead. Use for development or single-process scenarios.
- **file_lock()**: Small overhead for locking. Recommended for production multi-process environments.
- **sqlite()**: Structured storage with query capabilities. Ideal when you need to analyze logs programmatically.
- **system_log()**: Integrates with system logging infrastructure. Performance depends on syslog configuration.

## Security & Best Practices

### Path Validation

Always validate and sanitize file paths before passing them to logging functions if they come from user input:

```php
// ❌ Dangerous - user input directly used
$log_path = $_GET['log_file'];
info('User action', [], file_lock($log_path));

// ✅ Safe - validate against whitelist
$allowed_logs = [
    'app' => '/var/log/myapp/app.log',
    'errors' => '/var/log/myapp/errors.log',
];

$log_type = $_GET['log_type'] ?? 'app';
if (isset($allowed_logs[$log_type])) {
    info('User action', [], file_lock($allowed_logs[$log_type]));
}
```

### Sensitive Data

Be cautious about logging sensitive information. Context data is stored in plain text:

```php
// ❌ Avoid logging passwords, tokens, credit cards
error('Login failed', ['password' => $password]);

// ✅ Log non-sensitive identifiers only
error('Login failed', ['username' => $username, 'ip' => $ip_address]);
```

### Log Rotation

Implement external log rotation to prevent disk space issues. Using logrotate on Linux:

```bash
# /etc/logrotate.d/myapp
/var/log/myapp/*.log {
    daily
    rotate 7
    compress
    delaycompress
    missingok
    notifempty
    create 0640 www-data www-data
}
```

### File Permissions

Ensure log directories have appropriate permissions:

```bash
# Create log directory with proper permissions
mkdir -p /var/log/myapp
chmod 755 /var/log/myapp
chown www-data:www-data /var/log/myapp
```

### High-Throughput Scenarios

For high-throughput applications, consider:

1. **Use file_put() instead of file_lock()**: Avoids blocking on lock acquisition
2. **Use SQLite with WAL mode**: Better concurrency for database logging
3. **Batch writes**: Use custom media with buffering (see examples)
4. **Async logging**: Queue logs for background processing

```php
// Enable WAL mode for better SQLite concurrency
$db = new PDO('sqlite:/var/log/app.db');
$db->exec('PRAGMA journal_mode=WAL');
```

### Error Handling

Logger failures should never break your application. All built-in media handle errors gracefully:

```php
// Even if logging fails, your application continues
try {
    processPayment($order);
    info('Payment processed', ['order_id' => $order->id]);
} catch (PaymentException $e) {
    // Logging errors are caught internally and sent to error_log()
    critical('Payment failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
    throw $e;  // Re-throw business exception
}
```

## Creating Custom Media

You can create your own custom media types without waiting for new package versions. A media is simply a closure that accepts a `Message` and handles it however you want.

### Basic Custom Media Example

```php
use PhpRepos\Logger\Core\Data\Message;
use function PhpRepos\Logger\API\Config\set_default_media;
use function PhpRepos\Logger\API\Logs\info;

// Create a simple custom media that logs to a REST API
function api_logger(string $endpoint, string $api_key): Closure
{
    return function (Message $log) use ($endpoint, $api_key) {
        $data = [
            'level' => $log->level,
            'message' => $log->text,
            'context' => $log->context,
            'timestamp' => $log->time->format('c'),
            'id' => $log->id
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('[LOGGER] Failed to send log to API: HTTP ' . $httpCode);
        }
    };
}

// Use your custom media
set_default_media(api_logger('https://api.example.com/logs', 'your-api-key'));
info('User logged in', ['user_id' => 123]);
```

### Custom Media with Batching

```php
function batch_file_logger(string $path, int $batch_size = 100): Closure
{
    static $buffer = [];
    static $file_path = null;

    if ($file_path === null) {
        $file_path = $path;
    }

    return function (Message $log) use ($path, $batch_size, &$buffer) {
        $buffer[] = $log->jsonSerialize();

        // Flush when batch size is reached
        if (count($buffer) >= $batch_size) {
            $fp = fopen($path, 'a');
            if ($fp) {
                flock($fp, LOCK_EX);
                foreach ($buffer as $entry) {
                    fwrite($fp, json_encode($entry) . PHP_EOL);
                }
                flock($fp, LOCK_UN);
                fclose($fp);
                $buffer = []; // Clear buffer
            }
        }
    };
}

// Use batch logger
set_default_media(batch_file_logger('/var/log/batch.log', 50));
```

### Custom Media with Filtering

```php
function filtered_logger(callable $filter, callable $media): Closure
{
    return function (Message $log) use ($filter, $media) {
        // Only log if filter returns true
        if ($filter($log)) {
            $media($log);
        }
    };
}

// Only log errors and above
$error_only = filtered_logger(
    fn($log) => in_array($log->level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY']),
    file_lock('/var/log/errors.log')
);

set_default_media($error_only);
```

### Custom Media with Routing by Level

Route different log levels to different destinations - for example, critical errors to a database and regular logs to files:

```php
use function PhpRepos\Logger\API\Media\{file_lock, sqlite};

function level_router(): Closure
{
    return function (Message $log) {
        // Route critical logs to SQLite for durability and querying
        if (in_array($log->level, ['ERROR', 'CRITICAL', 'ALERT', 'EMERGENCY'])) {
            $db_media = sqlite('/var/log/errors.db');
            $db_media($log);
        } else {
            // Route other logs to filesystem for performance
            $file_media = file_lock('/var/log/app.log');
            $file_media($log);
        }
    };
}

// Use the router as default media
set_default_media(level_router());

// Now all ERROR/CRITICAL/ALERT/EMERGENCY go to SQLite
error('Database timeout', ['query' => 'SELECT...']);  // → SQLite

// Other levels go to filesystem
info('User logged in', ['user_id' => 123]);  // → File
debug('Cache hit', ['key' => 'user:123']);   // → File
```

### Custom Media for Multiple Destinations

```php
function multi_logger(callable ...$media): Closure
{
    return function (Message $log) use ($media) {
        foreach ($media as $medium) {
            try {
                $medium($log);
            } catch (\Exception $e) {
                error_log('[LOGGER] Media failed: ' . $e->getMessage());
            }
        };
    };
}

// Log to multiple destinations with error isolation
$multi = multi_logger(
    file_lock('/var/log/app.log'),
    sqlite('/var/log/app.db'),
    api_logger('https://api.example.com/logs', 'key')
);

set_default_media($multi);
```

### Custom Media Best Practices

1. **Return a Closure**: Media must be a closure that accepts a `Message`
2. **Handle Errors**: Always wrap risky operations in try-catch
3. **Fail Gracefully**: Log errors to stderr, don't throw exceptions
4. **Be Efficient**: Avoid blocking operations when possible
5. **Clean Up Resources**: Close connections, file handles, etc.

### Message Properties Available

When creating custom media, you have access to:

```php
function custom_media(): Closure
{
    return function (Message $log) {
        $log->id;        // string - UUID v4
        $log->level;     // string - EMERGENCY, ALERT, CRITICAL, etc.
        $log->text;      // string - The log message
        $log->context;   // array - Context data
        $log->time;      // DateTimeImmutable - Timestamp (UTC)

        // Or get everything as array
        $data = $log->jsonSerialize();
    };
}
```

## Contributing

Contributions are welcome! See [CONTRIBUTING.md](CONTRIBUTING.md) for architecture details and development guidelines.

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Links

- [GitHub Repository](https://github.com/php-repos/logger)
- [phpkg Package Manager](https://phpkg.com)
- [PSR-3 Logger Interface](https://www.php-fig.org/psr/psr-3/)
