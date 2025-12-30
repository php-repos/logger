# Upgrading from v1.x to v2.0

This guide provides step-by-step instructions for upgrading your application from Logger v1.x to v2.0.

## Overview

Version 2.0 reorganizes the package structure with clearer namespaces. The actual function behavior remains exactly the same - only the import statements need updating.

**Estimated upgrade time:** 5-10 minutes for most projects

## Breaking Changes

1. **Namespace reorganization** - All function imports need the `API\Logs\`, `API\Media\`, or `API\Config\` prefix
2. **Configuration API split** - `default_media()` replaced with `set_default_media()` and `get_default_media()`

## Step-by-Step Upgrade Process

### Step 1: Update Package

Run the phpkg update command:

```bash
phpkg update logger
phpkg build
```

### Step 2: Update Logging Function Imports

All logging functions now use the `API\Logs\` namespace.

**Before (v1.x):**
```php
use function PhpRepos\Logger\log;
use function PhpRepos\Logger\emergency;
use function PhpRepos\Logger\alert;
use function PhpRepos\Logger\critical;
use function PhpRepos\Logger\error;
use function PhpRepos\Logger\warning;
use function PhpRepos\Logger\notice;
use function PhpRepos\Logger\info;
use function PhpRepos\Logger\debug;
```

**After (v2.0):**
```php
use function PhpRepos\Logger\API\Logs\log;
use function PhpRepos\Logger\API\Logs\emergency;
use function PhpRepos\Logger\API\Logs\alert;
use function PhpRepos\Logger\API\Logs\critical;
use function PhpRepos\Logger\API\Logs\error;
use function PhpRepos\Logger\API\Logs\warning;
use function PhpRepos\Logger\API\Logs\notice;
use function PhpRepos\Logger\API\Logs\info;
use function PhpRepos\Logger\API\Logs\debug;
```

### Step 3: Update Media Function Imports

All media functions now use the `API\Media\` namespace.

**Before (v1.x):**
```php
use function PhpRepos\Logger\system_log;
use function PhpRepos\Logger\file_put;
use function PhpRepos\Logger\file_lock;
```

**After (v2.0):**
```php
use function PhpRepos\Logger\API\Media\system_log;
use function PhpRepos\Logger\API\Media\file_put;
use function PhpRepos\Logger\API\Media\file_lock;
use function PhpRepos\Logger\API\Media\sqlite;  // New in v2.0!
```

### Step 4: Update Configuration Function Imports

Configuration functions now use the `API\Config\` namespace and are split into setter/getter.

**Before (v1.x):**
```php
use function PhpRepos\Logger\default_media;

// Set default media
default_media(file_lock('/var/log/app.log'));

// Get default media
$media = default_media();
```

**After (v2.0):**
```php
use function PhpRepos\Logger\API\Config\set_default_media;
use function PhpRepos\Logger\API\Config\get_default_media;

// Set default media
set_default_media(file_lock('/var/log/app.log'));

// Get default media
$media = get_default_media();
```

**Multiple media (now variadic):**
```php
// Before
default_media(
    file_lock('/var/log/app.log'),
    system_log()
);

// After
set_default_media(
    file_lock('/var/log/app.log'),
    system_log()
);
```

### Step 5: Rebuild and Test

```bash
phpkg build
phpkg run test-runner  # If you have tests
```

## Quick Find-and-Replace Guide

Use your IDE's find-and-replace feature in this **exact order**:

1. `use function PhpRepos\Logger\log;` → `use function PhpRepos\Logger\API\Logs\log;`
2. `use function PhpRepos\Logger\emergency;` → `use function PhpRepos\Logger\API\Logs\emergency;`
3. `use function PhpRepos\Logger\alert;` → `use function PhpRepos\Logger\API\Logs\alert;`
4. `use function PhpRepos\Logger\critical;` → `use function PhpRepos\Logger\API\Logs\critical;`
5. `use function PhpRepos\Logger\error;` → `use function PhpRepos\Logger\API\Logs\error;`
6. `use function PhpRepos\Logger\warning;` → `use function PhpRepos\Logger\API\Logs\warning;`
7. `use function PhpRepos\Logger\notice;` → `use function PhpRepos\Logger\API\Logs\notice;`
8. `use function PhpRepos\Logger\info;` → `use function PhpRepos\Logger\API\Logs\info;`
9. `use function PhpRepos\Logger\debug;` → `use function PhpRepos\Logger\API\Logs\debug;`
10. `use function PhpRepos\Logger\system_log;` → `use function PhpRepos\Logger\API\Media\system_log;`
11. `use function PhpRepos\Logger\file_put;` → `use function PhpRepos\Logger\API\Media\file_put;`
12. `use function PhpRepos\Logger\file_lock;` → `use function PhpRepos\Logger\API\Media\file_lock;`
13. `use function PhpRepos\Logger\default_media;` → `use function PhpRepos\Logger\API\Config\set_default_media;`
14. `= default_media()` → `= get_default_media()`
15. `default_media(` → `set_default_media(`

**Important:** Apply replacements in the order shown to avoid conflicts.

## Automated Migration Script

For larger codebases, use this bash script:

```bash
#!/bin/bash

# Find all PHP files and update imports
find . -name "*.php" -type f -exec sed -i \
  -e 's/use function PhpRepos\\Logger\\log;/use function PhpRepos\\Logger\\API\\Logs\\log;/g' \
  -e 's/use function PhpRepos\\Logger\\emergency;/use function PhpRepos\\Logger\\API\\Logs\\emergency;/g' \
  -e 's/use function PhpRepos\\Logger\\alert;/use function PhpRepos\\Logger\\API\\Logs\\alert;/g' \
  -e 's/use function PhpRepos\\Logger\\critical;/use function PhpRepos\\Logger\\API\\Logs\\critical;/g' \
  -e 's/use function PhpRepos\\Logger\\error;/use function PhpRepos\\Logger\\API\\Logs\\error;/g' \
  -e 's/use function PhpRepos\\Logger\\warning;/use function PhpRepos\\Logger\\API\\Logs\\warning;/g' \
  -e 's/use function PhpRepos\\Logger\\notice;/use function PhpRepos\\Logger\\API\\Logs\\notice;/g' \
  -e 's/use function PhpRepos\\Logger\\info;/use function PhpRepos\\Logger\\API\\Logs\\info;/g' \
  -e 's/use function PhpRepos\\Logger\\debug;/use function PhpRepos\\Logger\\API\\Logs\\debug;/g' \
  -e 's/use function PhpRepos\\Logger\\system_log;/use function PhpRepos\\Logger\\API\\Media\\system_log;/g' \
  -e 's/use function PhpRepos\\Logger\\file_put;/use function PhpRepos\\Logger\\API\\Media\\file_put;/g' \
  -e 's/use function PhpRepos\\Logger\\file_lock;/use function PhpRepos\\Logger\\API\\Media\\file_lock;/g' \
  -e 's/use function PhpRepos\\Logger\\default_media;/use function PhpRepos\\Logger\\API\\Config\\set_default_media;\\nuse function PhpRepos\\Logger\\API\\Config\\get_default_media;/g' \
  -e 's/= default_media()/= get_default_media()/g' \
  -e 's/default_media(/set_default_media(/g' \
  {} \;

echo "Migration complete! Review changes and rebuild."
```

**Warning:** Commit your code before running automated scripts.

## Upgrade Checklist

- [ ] Ran `phpkg update logger`
- [ ] Ran `phpkg build`
- [ ] Updated logging function imports (`API\Logs\*`)
- [ ] Updated media function imports (`API\Media\*`)
- [ ] Updated config function imports (`API\Config\*`)
- [ ] Changed `default_media()` to `set_default_media()` / `get_default_media()`
- [ ] Rebuilt application
- [ ] Tested logging functionality
- [ ] Verified log files are created correctly
- [ ] Ready for deployment

## What Stays the Same

✅ All function signatures (same parameters)
✅ Logging behavior and output format
✅ Media behavior (file locking, system log, etc.)
✅ Error handling (now improved!)
✅ PSR-3 log levels
✅ File formats and database schemas

**Only the import statements change.**

## New Features in v2.0

After upgrading, you get these improvements automatically:

✅ **Better error handling** - All media functions now fall back to stderr on errors (never lose logs)
✅ **SQLite logging** - NEW! Log to SQLite databases with `sqlite('/path/to/db.db', 'table_name')`
✅ **Split configuration API** - Clearer setter/getter separation
✅ **Improved reliability** - Path validation happens upfront
✅ **Better error messages** - More context in exceptions

### Try the New SQLite Media

```php
use function PhpRepos\Logger\API\Media\sqlite;
use function PhpRepos\Logger\API\Config\set_default_media;
use function PhpRepos\Logger\API\Logs\info;

// Set SQLite as default media
set_default_media(sqlite('/var/log/app.db'));

// Or with custom table name
set_default_media(sqlite('/var/log/app.db', 'application_logs'));

// Log as usual
info('Application started', ['version' => '2.0']);
```

## Troubleshooting

### "Function not found" errors

**Problem:** `Call to undefined function PhpRepos\Logger\info()`

**Solution:** Add the `API\Logs\` namespace to function imports:
```php
use function PhpRepos\Logger\API\Logs\info;
```

### "Too few arguments" for default_media()

**Problem:** Calling `default_media()` without arguments to get media

**Solution:** Use the new getter function:
```php
// Before
$media = default_media();

// After
$media = get_default_media();
```

### Build fails after update

**Solution:**
```bash
rm -rf build
phpkg build
```

## Rollback

If you need to rollback to v1.x:

```bash
phpkg update logger@1.0.0
phpkg build
# Revert namespace changes using git
```

## Getting Help

- **Issues:** https://github.com/php-repos/logger/issues
- **Discussions:** https://github.com/php-repos/logger/discussions
- **Documentation:** See README.md and CONTRIBUTING.md

---

**Questions?** Open an issue and we'll help you upgrade!
