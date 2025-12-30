# Contributing to Logger Package

Thank you for your interest in contributing to the Logger package! This document provides guidelines and architectural context to help you contribute effectively.

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Development Setup](#development-setup)
- [Natural Architecture Pattern](#natural-architecture-pattern)
- [Code Organization](#code-organization)
- [Testing Strategy](#testing-strategy)
- [Coding Standards](#coding-standards)
- [Pull Request Process](#pull-request-process)

## Architecture Overview

This package follows **Natural Architecture**, a three-layer pattern that promotes clean separation of concerns:

```
┌─────────────────────────────────────┐
│         API Layer                   │  ← Client-facing interface
│  (PhpRepos\Logger\API\*)            │
├─────────────────────────────────────┤
│         Core Layer                  │  ← Business logic
│  (PhpRepos\Logger\Core\*)           │
├─────────────────────────────────────┤
│       Platform Layer                │  ← Primitives & platform abstraction
│  (PhpRepos\Logger\Platform\*)       │
└─────────────────────────────────────┘
```

## Development Setup

### Requirements

- PHP 8.3 or higher
- [phpkg](https://phpkg.com) package manager
- PDO SQLite extension

### Installation

```bash
# Clone the repository
git clone https://github.com/php-repos/logger.git
cd logger

# Build the project
phpkg build

# Run tests
phpkg run test-runner
```

## Natural Architecture Pattern

### API Layer (`Source/API/`)

**Purpose:** Provides the client-facing interface. This is what users import and call.

**Rules:**
- ✅ Can call Core layer functions
- ✅ Should handle user-facing concerns (configuration, convenience)
- ✅ Should include comprehensive error handling with fallbacks
- ❌ Cannot call Platform layer directly
- ❌ Should not contain business logic

**Files:**
- `Config.php` - Configuration functions (`set_default_media`, `get_default_media`)
- `Logs.php` - Logging functions (`log`, `info`, `error`, `debug`, etc.)
- `Media.php` - Media factory functions (`system_log`, `file_put`, `file_lock`, `sqlite`)

**Example Pattern:**
```php
function file_lock(string $path): Closure
{
    // Validate path once on first call
    once($cache_key, fn () => ensure_exists($path));

    // Return closure with error handling
    return function (Message $log) use ($path) {
        try {
            lock_and_write($path, $log);  // Call Core layer
        } catch (\Exception $e) {
            // Fallback to stderr - never lose logs
            error_log('[LOGGER] Failed: ' . $e->getMessage());
            error_log('[LOGGER] Original: ' . $log->text);
        }
    };
}
```

### Core Layer (`Source/Core/`)

**Purpose:** Contains business logic and orchestrates Platform primitives.

**Rules:**
- ✅ Can call Platform layer functions
- ✅ Contains business logic and domain concepts
- ✅ Throws meaningful exceptions
- ❌ Cannot call API layer
- ❌ Should not contain platform-specific code

**Files:**
- `Caches.php` - Simple in-memory caching (`set`, `get`, `has`, `once`)
- `Databases.php` - SQLite database operations (`sqlite_create_table`, `sqlite_write`)
- `Files.php` - File operations orchestration (`ensure_exists`, `lock_and_write`, `put_in_file`)
- `Messages.php` - Message creation and encoding (`create`, `encode`, `validate`)
- `Syslogs.php` - System log operations (`write`, `map_level_to_priority`)
- `Data/Level.php` - Level enum (class)
- `Data/Message.php` - Message data class
- `Exceptions/*.php` - Domain exceptions

**Example Pattern:**
```php
function lock_and_write(string $path, Message $message): bool
{
    // Validate using Core function
    if (!validate($message)) {
        throw new JsonEncodeException('Cannot encode', $message);
    }

    // Use Platform primitives
    $handler = open($path, 'a');  // Platform
    if (!lock($handler, LOCK_EX)) {  // Platform
        close($handler);  // Platform
        throw new FileLockException('Cannot lock', $path);
    }

    try {
        $json = encode($message);  // Core
        write($handler, $json . PHP_EOL);  // Platform
        lock($handler, LOCK_UN);  // Platform
        close($handler);  // Platform
        return true;
    } catch (\Exception $e) {
        lock($handler, LOCK_UN);  // Platform
        close($handler);  // Platform
        throw $e;
    }
}
```

### Platform Layer (`Source/Platform/`)

**Purpose:** Provides primitive operations and platform abstraction.

**Rules:**
- ✅ Wraps native PHP functions
- ✅ Provides thin abstractions
- ✅ Can be swapped for testing or portability
- ❌ Cannot call Core or API layers
- ❌ Should not contain business logic

**Files:**
- `DateTimes.php` - Date/time primitives (`now`)
- `Filesystem.php` - File system primitives (`open`, `lock`, `write`, `close`, `append`, `exists`, `is_directory`, `is_writable_directory`, `mkdir`)
- `Jsons.php` - JSON primitives (`encode`, `validate`)
- `Sqlite.php` - SQLite primitives (`open`, `exec`, `insert`)
- `Strings.php` - String primitives (`uuid_v4`)
- `System.php` - System primitives (`write` for syslog)

**Example Pattern:**
```php
function is_writable_directory(string $path): bool
{
    // Simple wrapper around native PHP
    return is_dir($path) && is_writable($path);
}

function mkdir(string $path, int $permissions = 0777, bool $recursive = true): bool
{
    // Error suppression for cleaner API at higher layers
    return @\mkdir($path, $permissions, $recursive);
}
```

## Code Organization

### Directory Structure

```
Source/
├── API/
│   ├── Config.php          # Configuration API
│   ├── Logs.php            # Logging API
│   └── Media.php           # Media factories
├── Core/
│   ├── Data/
│   │   ├── Level.php       # Level enum (class)
│   │   └── Message.php     # Message class
│   ├── Exceptions/
│   │   ├── FileException.php
│   │   ├── FileLockException.php
│   │   ├── FileWriteException.php
│   │   ├── JsonEncodeException.php
│   │   └── SqliteException.php
│   ├── Caches.php          # Caching logic
│   ├── Databases.php       # Database logic
│   ├── Files.php           # File logic
│   ├── Messages.php        # Message logic
│   └── Syslogs.php         # Syslog logic
└── Platform/
    ├── DateTimes.php       # Date/time primitives
    ├── Filesystem.php      # Filesystem primitives
    ├── Jsons.php           # JSON primitives
    ├── Sqlite.php          # SQLite primitives
    ├── Strings.php         # String primitives
    └── System.php          # System primitives

Tests/
├── ConfigTest.php          # Config API tests
├── LogsTest.php            # Logs API tests
├── MediaTest.php           # File media tests
├── MessageTest.php         # Message tests
└── SqliteMediaTest.php     # SQLite media tests
```

### Naming Conventions

- **Functions:** `snake_case` (following PHP community standard for functional programming)
- **Classes:** `PascalCase`
- **Constants:** `SCREAMING_SNAKE_CASE`
- **Namespaces:** `PhpRepos\Logger\{API|Core|Platform}\{Domain}`

## Testing Strategy

### Test Philosophy

We test **features**, not layers. Tests are written from the user's perspective, ensuring that:

1. **Features work end-to-end** - Tests call API functions and verify outcomes
2. **No unused code** - Every Core and Platform function must be used by a feature
3. **Fail-fast validation** - Errors are caught immediately, not silently ignored
4. **Cross-platform compatibility** - Tests run on Linux, Windows, and macOS

### What We Test

✅ **Feature Tests** (API-level integration tests):
- Logging with different levels
- Multiple media destinations
- Error handling and fallbacks
- Configuration changes
- File creation and permissions
- Database operations
- Context serialization

### What We Don't Test

❌ **Unit tests for Core/Platform** - These layers are tested indirectly through feature tests
❌ **Mock/stub heavy tests** - We test real behavior, not mocks
❌ **Implementation details** - Tests shouldn't break when refactoring internals

### Test Structure

```php
use function PhpRepos\TestRunner\Runner\test;
use function PhpRepos\TestRunner\Assertions\{assert_true, assert_false};

test(
    title: 'it should log to multiple media',
    case: function () {
        $media1 = file_put('/tmp/test1.log');
        $media2 = file_lock('/tmp/test2.log');

        info('test message', [], $media1, $media2);

        assert_true(str_contains(file_get_contents('/tmp/test1.log'), 'test message'));
        assert_true(str_contains(file_get_contents('/tmp/test2.log'), 'test message'));
    },
    after: function () {
        @unlink('/tmp/test1.log');
        @unlink('/tmp/test2.log');
    }
);
```

### Writing Tests

When adding a new feature:

1. **Write feature test first** - Test the API-level behavior
2. **Ensure cross-platform paths** - Use `sys_get_temp_dir()` for temp files
3. **Clean up resources** - Use `after:` callbacks to delete files/databases
4. **Test error cases** - Verify exceptions are thrown when appropriate
5. **Test fallback behavior** - Ensure errors don't crash the app

### Running Tests

```bash
# Build and test
phpkg build && phpkg run test-runner
```

## Coding Standards

### General Principles

1. **Explicit over implicit** - Be clear about what code does
2. **Simple over clever** - Readable code is maintainable code
3. **Fail fast** - Validate early, throw exceptions for invalid state
4. **No silent failures** - Always log errors, even if you can't fix them
5. **Function-based design** - Prefer pure functions over stateful classes

### Function Guidelines

**Do:**
- Keep functions small and focused
- Use descriptive names (e.g., `is_writable_directory`, not `check_dir`)
- Document complex logic with inline comments
- Throw exceptions for invalid input
- Return early to reduce nesting

**Don't:**
- Create God functions that do everything
- Mutate parameters
- Use global state (except for controlled caches)
- Suppress errors without fallback handling
- Add unnecessary abstraction

### Documentation

Every public function should have a docblock with:

```php
/**
 * One-line summary.
 *
 * Detailed description if needed. Explain the "why", not just the "what".
 *
 * @param string $path The file path to validate.
 * @return bool True if valid.
 * @throws FileException If the path is invalid.
 *
 * @example
 * if (is_writable_directory('/var/log')) {
 *     // Directory is ready for logging
 * }
 */
function is_writable_directory(string $path): bool
```

### Error Handling

**API Layer:**
```php
try {
    core_function();
} catch (\Exception $e) {
    // Fallback - log to stderr, never lose the message
    error_log('[LOGGER] Error: ' . $e->getMessage());
}
```

**Core Layer:**
```php
if (!validate($input)) {
    // Throw meaningful exception
    throw new JsonEncodeException('Cannot encode message', $input);
}
```

**Platform Layer:**
```php
// Use error suppression for cleaner Core API
return @mkdir($path, 0777, true);
```

## Pull Request Process

### Before Submitting

1. **Run tests** - `phpkg run test-runner` must pass
2. **Build successfully** - `phpkg build` must complete
3. **Update documentation** - Update README.md if adding features
4. **Add tests** - New features need feature tests
5. **Check for unused code** - All Core/Platform functions must be used

### PR Guidelines

**Title Format:**
- `feat: Add custom table name support to SQLite media`
- `fix: Handle file locking errors gracefully`
- `docs: Update SQLite examples in README`
- `refactor: Consolidate filesystem functions`

**Description Should Include:**
- What problem does this solve?
- How does it solve it?
- Are there breaking changes?
- How to test it?

**Example:**
```markdown
## Problem
Users cannot specify custom table names for SQLite logging.

## Solution
Added optional `$table_name` parameter to `sqlite()` function.

## Breaking Changes
None - parameter is optional with default value 'logs'.

## Testing
```php
info('test', [], sqlite('/tmp/test.db', 'custom_logs'));
// Check table name in database
```
```

### Review Process

1. **Automated checks** - Tests must pass
2. **Code review** - Maintainer reviews architecture and code quality
3. **Discussion** - Address feedback or discuss alternatives
4. **Merge** - Once approved, PR is merged

### Breaking Changes

Breaking changes require:
- Clear documentation of what breaks
- Migration guide for users
- Major version bump (v1.x.x → v2.0.0)

## Common Patterns

### Adding a New Media Type

1. **Create factory in API/Media.php:**
```php
function my_media(string $config): Closure
{
    // Validate config once
    once($cache_key, fn () => validate_config($config));

    return function (Message $log) use ($config) {
        try {
            core_write_function($config, $log);
        } catch (\Exception $e) {
            error_log('[LOGGER] Failed: ' . $e->getMessage());
        }
    };
}
```

2. **Add Core orchestration function if needed:**
```php
function core_write_function(string $config, Message $message): bool
{
    // Orchestrate Platform primitives
    $resource = platform_open($config);
    platform_write($resource, encode($message));
    platform_close($resource);
    return true;
}
```

3. **Add Platform primitives if needed:**
```php
function platform_open(string $config) { /* ... */ }
function platform_write($resource, string $data): bool { /* ... */ }
function platform_close($resource): bool { /* ... */ }
```

4. **Add feature tests:**
```php
test(
    title: 'it should write using my_media',
    case: function () {
        info('test', [], my_media('/tmp/test'));
        // Verify behavior
    }
);
```

### Adding a New Log Level

Currently using PSR-3 standard levels. If adding custom levels:

1. Update `Core/Data/Level.php` enum
2. Add convenience function to `API/Logs.php`
3. Update `Core/Syslogs.php` priority mapping if needed
4. Add tests to `Tests/LogsTest.php`

## Questions?

- **GitHub Issues:** https://github.com/php-repos/logger/issues
- **Discussions:** https://github.com/php-repos/logger/discussions

We welcome questions about architecture, design decisions, or contribution process!

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
