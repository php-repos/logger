<?php

namespace PhpRepos\Logger\Core\Messages;

use PhpRepos\Logger\Core\Data\Message;
use PhpRepos\Logger\Core\Exceptions\JsonEncodeException;
use function PhpRepos\Logger\Platform\Jsons\encode as platform_encode;
use function PhpRepos\Logger\Platform\Jsons\validate as platform_validate;
use function PhpRepos\Logger\Platform\Strings\uuid_v4;
use function PhpRepos\Logger\Platform\DateTimes\now;

/**
 * Creates a log message.
 *
 * Factory function that creates a Message instance using Platform primitives
 * for ID generation (UUID v4) and timestamp (current UTC time).
 *
 * @param string $level Log level (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG).
 * @param string $text The log message text.
 * @param array $context Optional context data (default: []).
 * @return Message The created message instance.
 *
 * @example
 * $message = create('INFO', 'User logged in', ['user_id' => 123]);
 */
function create(string $level, string $text, array $context = []): Message
{
    return new Message(
        uuid_v4(),
        $level,
        $text,
        $context,
        now()
    );
}

/**
 * Business logic for JSON encoding with error context.
 *
 * Encodes a log message to JSON with comprehensive error handling.
 * Throws JsonEncodeException with detailed context if encoding fails.
 *
 * @param Message $message Message to encode.
 * @return string JSON encoded string.
 * @throws JsonEncodeException If the message cannot be JSON encoded.
 *
 * @example
 * $message = Message::info('User logged in', ['user_id' => 123]);
 * $json = encode($message);
 * // Returns: '{"id":"...","level":"INFO","message":"User logged in",...}'
 */
function encode(Message $message): string
{
    $data = $message->jsonSerialize();

    $json = platform_encode($data);

    if ($json === false) {
        $error = json_last_error_msg();
        throw new JsonEncodeException(
            'Failed to encode message to JSON',
            $data,
            $error
        );
    }

    return $json;
}

/**
 * Validates if a message can be JSON encoded.
 *
 * Checks if the message can be safely JSON encoded without errors.
 * This is a business-level validation that uses the platform layer.
 *
 * @param Message $message Message to validate.
 * @return bool True if valid, false otherwise.
 *
 * @example
 * $message = Message::info('Test');
 * if (validate($message)) {
 *     // Safe to encode
 * }
 */
function validate(Message $message): bool
{
    return platform_validate($message->jsonSerialize());
}
