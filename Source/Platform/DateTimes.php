<?php

namespace PhpRepos\Logger\Platform\DateTimes;

use DateTimeImmutable;
use DateTimeZone;

/**
 * Returns current date and time in UTC timezone.
 *
 * Creates a DateTimeImmutable object representing the current moment
 * in UTC timezone.
 *
 * @return DateTimeImmutable Current date and time in UTC
 *
 * @example
 * $timestamp = now();
 * echo $timestamp->format('c'); // ISO 8601 format
 */
function now(): DateTimeImmutable
{
    return new DateTimeImmutable('now', new DateTimeZone('UTC'));
}
