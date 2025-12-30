# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-12-30

### Added
- **SQLite Custom Table Names**: The `sqlite()` media function now accepts an optional `$table_name` parameter to customize the database table name (defaults to `'logs'`)
- **Configuration Functions**: Split configuration into two separate functions:
  - `set_default_media(...$media)` - Set default media destinations
  - `get_default_media()` - Get currently configured media
- **Convenience Logging Functions**: Added PSR-3 compatible logging functions:
  - `emergency()`, `alert()`, `critical()`, `error()`, `warning()`, `notice()`, `info()`, `debug()`
- **Error Handling**: All media functions (`file_put`, `file_lock`, `sqlite`, `system_log`) now include automatic error handling with stderr fallback to prevent log loss
- **Documentation**:
  - Comprehensive README.md with examples for all features
  - CONTRIBUTING.md with architecture guidelines and testing philosophy
  - Inline PHPDoc with usage examples for all public functions

### Changed
- **BREAKING**: Namespace restructure to Natural Architecture pattern:
  - `PhpRepos\Logger\Log\Level` → `PhpRepos\Logger\Core\Data\Level`
  - `PhpRepos\Logger\Log\Message` → `PhpRepos\Logger\Core\Data\Message`
  - `PhpRepos\Logger\{function}` → `PhpRepos\Logger\API\Logs\{function}`
  - `PhpRepos\Logger\Media\{function}` → `PhpRepos\Logger\API\Media\{function}`
  - `PhpRepos\Logger\Config\{function}` → `PhpRepos\Logger\API\Config\{function}`
- **BREAKING**: Configuration API changed:
  - `default_media($media)` → `set_default_media(...$media)` (now variadic)
  - Getter: `get_default_media()` (new separate function)
- **BREAKING**: SQLite media function signature:
  - `sqlite(string $path)` → `sqlite(string $path, ?string $table_name = null)`
- **Improved Reliability**: All media functions now catch exceptions and fall back to stderr logging, ensuring log messages are never silently lost

### Fixed
- File creation now properly validates parent directory exists and is writable before attempting write
- SQLite table creation now happens once during media configuration (not on first log), providing immediate feedback on configuration errors
- File locking now properly releases locks even when write operations fail
- JSON encoding validates data before attempting encode to provide clearer error messages

### Security
- File operations validate directory permissions before attempting writes
- All database inputs properly validated before insertion
- Context data validated before JSON encoding to prevent runtime errors

## [1.0.0] - Initial Release

### Added
- Basic logging functionality with PSR-3 log levels
- File-based logging with append support
- System log (syslog) integration
- Simple configuration system
- Message data structure with level, text, context, and timestamp
- Basic test coverage

---

## Migration Guide: v1.x to v2.0

### Namespace Changes

**Before (v1.x):**
```php
use PhpRepos\Logger\Log\Level;
use PhpRepos\Logger\Log\Message;
use function PhpRepos\Logger\log;
use function PhpRepos\Logger\info;
```

**After (v2.0):**
```php
use PhpRepos\Logger\Core\Data\Level;
use PhpRepos\Logger\Core\Data\Message;
use function PhpRepos\Logger\API\Logs\log;
use function PhpRepos\Logger\API\Logs\info;
use function PhpRepos\Logger\API\Config\set_default_media;
use function PhpRepos\Logger\API\Media\file_lock;
```

### Configuration Changes

**Before (v1.x):**
```php
default_media(file_put('/var/log/app.log'));
$media = default_media(); // Get current media
```

**After (v2.0):**
```php
set_default_media(file_lock('/var/log/app.log'));
$media = get_default_media(); // Get current media
```

### SQLite Usage

**Before (v1.x):**
```php
sqlite('/var/log/app.db'); // Fixed table name 'logs'
```

**After (v2.0):**
```php
sqlite('/var/log/app.db'); // Default table 'logs'
sqlite('/var/log/app.db', 'custom_logs'); // Custom table name
```

### Error Handling

**Before (v1.x):**
- Silent failures possible
- Limited error context

**After (v2.0):**
- All media functions include try-catch with stderr fallback
- Rich exceptions with context (file paths, error details)
- Never lose log messages - always falls back to stderr

### Testing

**Before (v1.x):**
- Mixed test approaches

**After (v2.0):**
- Feature-based testing philosophy
- All tests written from user perspective
- Tests verify API layer behavior, not implementation details

---

## Notes

- **v2.0.0** is a major release with namespace changes and API improvements
- Function behavior remains the same, but namespace imports must be updated
- All changes improve reliability, developer experience, and code maintainability
- See the Migration Guide above for step-by-step upgrade instructions
