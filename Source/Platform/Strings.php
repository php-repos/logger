<?php

namespace PhpRepos\Logger\Platform\Strings;

use Ramsey\Uuid\Uuid;

/**
 * Generates a UUID v4 string.
 *
 * Uses Ramsey\Uuid library to generate a universally unique identifier.
 *
 * @return string UUID v4 string (e.g., "550e8400-e29b-41d4-a716-446655440000")
 *
 * @example
 * $id = uuid_v4();
 * // Returns: "a1b2c3d4-e5f6-7890-abcd-ef1234567890"
 */
function uuid_v4(): string
{
    return Uuid::uuid4()->toString();
}
