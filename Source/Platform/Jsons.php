<?php

namespace PhpRepos\Logger\Platform\Jsons;

/**
 * Low-level JSON validation check.
 *
 * Checks if the input can be safely JSON encoded without errors.
 * Uses native json_validate() if available (PHP 8.3+), otherwise
 * falls back to attempting json_encode with error handling.
 *
 * @param mixed $input Input to validate for JSON encoding.
 * @return bool True if the input can be JSON encoded, false otherwise.
 *
 * @example
 * validate(['key' => 'value']); // Returns true
 * validate(fopen('file.txt', 'r')); // Returns false (resource cannot be encoded)
 */
function validate(mixed $input): bool
{
    // Try to encode the input to validate if it can be JSON encoded
    try {
        json_encode($input, JSON_THROW_ON_ERROR);
        return true;
    } catch (\Exception) {
        return false;
    }
}

/**
 * Low-level JSON encoding.
 *
 * Encodes the input data to a JSON string. Returns false on failure.
 *
 * @param mixed $input Input to encode as JSON.
 * @return string|false JSON string on success, false on failure.
 *
 * @example
 * encode(['level' => 'INFO', 'message' => 'Hello']);
 * // Returns: '{"level":"INFO","message":"Hello"}'
 */
function encode(mixed $input): string|false
{
    return json_encode($input);
}
