<?php

namespace PhpRepos\Logger\Core\Data;

use DateTimeImmutable;
use JsonSerializable;

/**
 * Represents a log message with associated metadata.
 *
 * This is a pure data class that encapsulates a log message, including its
 * unique identifier, severity level, message text, contextual data, and timestamp.
 * It implements JsonSerializable to allow conversion to a JSON-compatible array.
 *
 * Note: This class has no static factory methods. Use Core\Messages\create()
 * to create new message instances.
 */
class Message implements JsonSerializable
{
    /**
     * Constructs a new log message.
     *
     * @param string $id Unique identifier for the log message (UUID v4).
     * @param string $level The severity level (EMERGENCY, ALERT, CRITICAL, ERROR, WARNING, NOTICE, INFO, DEBUG).
     * @param string $text The log message content.
     * @param array $context Additional contextual data for the log message.
     * @param DateTimeImmutable $time The timestamp of the log message (UTC).
     */
    public function __construct(
        public readonly string $id,
        public readonly string $level,
        public readonly string $text,
        public readonly array $context,
        public readonly DateTimeImmutable $time,
    ) {}

    /**
     * Serializes the log message to a JSON-compatible array.
     *
     * @return array An array containing the log message's id, level, message, context, and time.
     *
     * @example
     * $message = Message::info("User logged in", ["user_id" => 123]);
     * $json = json_encode($message);
     * // Produces JSON: {"id":"uuid","level":"INFO","message":"User logged in","context":{"user_id":123},"time":"2025-05-30T15:41:00.000000+00:00"}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'level' => $this->level,
            'message' => $this->text,
            'context' => $this->context,
            'time' => $this->time->format('Y-m-d\TH:i:s.uP'),
        ];
    }
}
