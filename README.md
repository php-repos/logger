# Logger Package

A lightweight, flexible logging package for PHP applications, designed for use with the `phpkg` package manager. This package provides a simple way to log messages with different severity levels to various output media, such as the system log or files. It supports PSR-3 inspired log levels and allows customizable logging media through closures.

## Features

- **PSR-3 Inspired Log Levels**: Supports eight log levels (`EMERGENCY`, `ALERT`, `CRITICAL`, `ERROR`, `WARNING`, `NOTICE`, `INFO`, `DEBUG`).
- **Flexible Media**: Log messages to multiple outputs (e.g., system log, files) using customizable media closures.
- **JSON Serialization**: Log messages can be serialized to JSON for structured logging.
- **Thread-Safe File Logging**: Includes support for file logging with exclusive locking.
- **Configurable Default Media**: Set default logging media for easy integration.
- **Lightweight and Simple**: Minimal dependencies and straightforward API.
- **Designed for `phpkg`**: Seamlessly integrates with the `phpkg` package manager for dependency management.

## Installation

### Requirements

- PHP 8.2 or higher
- [phpkg](https://phpkg.com) package manager

### Via `phpkg`

Add the package to your project using `phpkg`:

```bash
phpkg add https://github.com/php-repos/logger.git
```

## Usage

### Creating a Log Message

The `Message` class represents a log message with a unique ID, severity level, text, context, and timestamp.

```php
use PhpRepos\Logger\Log\Message;
use PhpRepos\Logger\Log\Level;

$message = Message::create(Level::INFO, "User logged in", ["user_id" => 123]);
```

Alternatively, use convenience methods for specific log levels:

```php
use PhpRepos\Logger\Log\Message;

$message = Message::info("Application started");
$message = Message::error("Database connection failed", ["db" => "main"]);
```

### Logging to Default Media

The `log` function sends messages to the default media (system log by default) if no specific media are provided.

```php
use PhpRepos\Logger\Log\Message;
use function PhpRepos\Logger\Logs\log;

log(Message::info("Application started", ["app" => "MyApp"]));
```

### Setting Custom Default Media

Set custom default media using `default_media`:

```php
use PhpRepos\Logger\Logs;
use function PhpRepos\Logger\Medias\file_put;

$media = file_put('/var/log/myapp.log');
Logs\default_media($media);

log(Message::warning("High memory usage", ["usage" => "85%"]));
// Logs to /var/log/myapp.log
```

### Logging to Specific Media

Log to specific media by passing them to the `log` function:

```php
use PhpRepos\Logger\Log\Message;
use function PhpRepos\Logger\Logs\log;
use function PhpRepos\Logger\Medias\file_lock;

$media = file_lock('/var/log/myapp.log');
log(Message::error("Database error", ["db" => "main"]), $media);
```

### Logging to Multiple Media

Log to multiple media simultaneously:

```php
use PhpRepos\Logger\Log\Message;
use function PhpRepos\Logger\Logs\log;
use function PhpRepos\Logger\Medias\system_log;
use function PhpRepos\Logger\Medias\file_put;

log(
    Message::critical("Server down", ["server_id" => "srv01"]),
    system_log(),
    file_put('/var/log/myapp.log')
);
```

### JSON Serialization

Log messages can be serialized to JSON for structured logging:

```php
use PhpRepos\Logger\Log\Message;

$message = Message::info("User logged in", ["user_id" => 123]);
$json = json_encode($message);
// Produces: {"id":"uuid","level":"INFO","message":"User logged in","context":{"user_id":123},"time":"2025-05-30T15:41:00.000000+00:00"}
```

## API Documentation

### `PhpRepos\Logger\Log\Level`

An enum defining the supported log levels.

- **Cases**: `EMERGENCY`, `ALERT`, `CRITICAL`, `ERROR`, `WARNING`, `NOTICE`, `INFO`, `DEBUG`
- **Example**:
  ```php
  use PhpRepos\Logger\Log\Level;
  $level = Level::INFO;
  ```

### `PhpRepos\Logger\Log\Message`

A class representing a log message.

- **Constructor**:
  ```php
  public function __construct(
      string $id,
      Level $level,
      string $text,
      array $context,
      DateTimeImmutable $time
  )
  ```
- **Static Methods**:
  - `create(Level $level, string $text, ?array $context = []): static`
  - Convenience methods: `alert`, `critical`, `debug`, `emergency`, `error`, `info`, `notice`, `warning`
- **Implements**: `JsonSerializable`
- **Example**:
  ```php
  $message = Message::create(Level::ERROR, "Failed to connect", ["retry" => 3]);
  ```

### `PhpRepos\Logger\Medias`

Provides logging media closures.

- **Functions**:
  - `system_log(): Closure`: Logs to the system log using `syslog`.
  - `file_put(string $path): Closure`: Logs to a file without locking.
  - `file_lock(string $path): Closure`: Logs to a file with exclusive locking.
- **Example**:
  ```php
  $fileLogger = file_lock('/var/log/myapp.log');
  $fileLogger(Message::info("Test log"));
  ```

### `PhpRepos\Logger\Logs`

Manages logging operations.

- **Functions**:
  - `default_media(callable ...$medias): array`: Sets or retrieves default logging media.
  - `log(Message $message, callable ...$medias): void`: Logs a message to specified or default media.
- **Example**:
  ```php
  Logs\default_media(file_put('/var/log/app.log'));
  log(Message::info("App started"));
  ```

## Contributing

Contributions are welcome! Please submit issues or pull requests to the [GitHub repository](https://github.com/php-repos/logger).

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.