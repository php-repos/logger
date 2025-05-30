<?php

namespace PhpRepos\Logger\Log;

use DateTimeImmutable;
use DateTimeZone;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

/**
 * Represents a log message with associated metadata.
 *
 * This class encapsulates a log message, including its unique identifier,
 * severity level, message text, contextual data, and timestamp. It implements
 * JsonSerializable to allow conversion to a JSON-compatible array.
 */
class Message implements JsonSerializable
{
    /**
     * Constructs a new log message.
     *
     * @param string $id Unique identifier for the log message.
     * @param Level $level The severity level of the log message.
     * @param string $text The log message content.
     * @param array $context Additional contextual data for the log message.
     * @param DateTimeImmutable $time The timestamp of the log message.
     */
    public function __construct(
        public readonly string $id,
        public readonly Level $level,
        public readonly string $text,
        public readonly array $context,
        public readonly DateTimeImmutable $time,
    ) {}

    /**
     * Creates a new log message with the specified level, text, and context.
     *
     * @param Level $level The severity level of the log message.
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class.
     *
     * @example
     * $message = Message::create(Level::INFO, "User logged in", ["user_id" => 123]);
     * // Creates a log message with INFO level, message text, and context.
     */
    public static function create(Level $level, string $text, ?array $context = []): static
    {
        return new static(Uuid::uuid4()->toString(), $level, $text, $context, new DateTimeImmutable('now', new DateTimeZone('UTC')));
    }

    /**
     * Creates a log message with ALERT level.
     *
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class with ALERT level.
     *
     * @example
     * $message = Message::alert("System overload detected", ["server_id" => "srv01"]);
     * // Creates an ALERT log message with context.
     */
    public static function alert(string $text, ?array $context = []): static
    {
        return static::create(Level::ALERT, $text, $context);
    }

    /**
     * Creates a log message with CRITICAL level.
     *
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class with CRITICAL level.
     */
    public static function critical(string $text, ?array $context = []): static
    {
        return static::create(Level::CRITICAL, $text, $context);
    }

    /**
     * Creates a log message with DEBUG level.
     *
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class with DEBUG level.
     *
     * @example
     * $message = Message::debug("Variable value", ["value" => $variable]);
     * // Creates a DEBUG log message with context.
     */
    public static function debug(string $text, ?array $context = []): static
    {
        return static::create(Level::DEBUG, $text, $context);
    }

    /**
     * Creates a log message with EMERGENCY level.
     *
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class with EMERGENCY level.
     */
    public static function emergency(string $text, ?array $context = []): static
    {
        return static::create(Level::EMERGENCY, $text, $context);
    }

    /**
     * Creates a log message with ERROR level.
     *
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class with ERROR level.
     */
    public static function error(string $text, ?array $context = []): static
    {
        return static::create(Level::ERROR, $text, $context);
    }

    /**
     * Creates a log message with INFO level.
     *
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class with INFO level.
     *
     * @example
     * $message = Message::info("Application started");
     * // Creates an INFO log message without context.
     */
    public static function info(string $text, ?array $context = []): static
    {
        return static::create(Level::INFO, $text, $context);
    }

    /**
     * Creates a log message with NOTICE level.
     *
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class with NOTICE level.
     */
    public static function notice(string $text, ?array $context = []): static
    {
        return static::create(Level::NOTICE, $text, $context);
    }

    /**
     * Creates a log message with WARNING level.
     *
     * @param string $text The log message content.
     * @param array|null $context Optional contextual data (default is empty array).
     * @return static A new instance of the Message class with WARNING level.
     */
    public static function warning(string $text, ?array $context = []): static
    {
        return static::create(Level::WARNING, $text, $context);
    }

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
            'level' => $this->level->value,
            'message' => $this->text,
            'context' => $this->context,
            'time' => $this->time->format('Y-m-d\TH:i:s.uP'),
        ];
    }
}
