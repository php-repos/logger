<?php

use PhpRepos\Logger\Core\Data\Message;
use function PhpRepos\Logger\Core\Messages\create;
use function PhpRepos\TestRunner\Assertions\assert_false;
use function PhpRepos\TestRunner\Assertions\assert_true;
use function PhpRepos\TestRunner\Runner\test;

test(
    title: 'it should construct a message',
    case: function () {
        $id = 'message-id';
        $level = 'ALERT';
        $text = 'hello world';
        $context = ['context' => 'data'];
        $time = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $message = new Message($id, $level, $text, $context, $time);

        $expected = [
            'id' => $id,
            'level' => $level,
            'message' => $text,
            'context' => $context,
            'time' => $time->format('Y-m-d\TH:i:s.uP'),
        ];

        assert_true($expected === $message->jsonSerialize(), 'Message JSON serialization does not match expected data.');
    }
);

test(
    title: 'Message::create factory method generates a valid message',
    case: function () {
        $level = 'CRITICAL';
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create($level, $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === $level, "Message level does not match; expected '$level', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'Message::create factory method generates a valid message without context',
    case: function () {
        $level = 'ALERT';
        $text = 'hello world log';

        $message = create($level, $text);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === $level, "Message level does not match; expected '$level', got '{$message->level}'.");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'it should create an alert message',
    case: function () {
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create('ALERT', $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === 'ALERT', "Message level does not match; expected 'ALERT', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'it should create a critical message',
    case: function () {
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create('CRITICAL', $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === 'CRITICAL', "Message level does not match; expected 'CRITICAL', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'it should create a debug message',
    case: function () {
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create('DEBUG', $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === 'DEBUG', "Message level does not match; expected 'DEBUG', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'it should create an emergency message',
    case: function () {
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create('EMERGENCY', $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === 'EMERGENCY', "Message level does not match; expected 'EMERGENCY', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'it should create an error message',
    case: function () {
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create('ERROR', $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === 'ERROR', "Message level does not match; expected 'ERROR', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'it should create a info message',
    case: function () {
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create('INFO', $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === 'INFO', "Message level does not match; expected 'INFO', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'it should create a notice message',
    case: function () {
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create('NOTICE', $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === 'NOTICE', "Message level does not match; expected 'NOTICE', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);

test(
    title: 'it should create a warning message',
    case: function () {
        $text = 'hello world log';
        $context = ['variables' => 'that are involved'];

        $message = create('WARNING', $text, $context);

        assert_true(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $message->id) === 1, "Message ID is not a valid UUID; got '{$message->id}'.");
        assert_true($message->text === $text, "Message title does not match; expected '$text', got '{$message->text}'.");
        assert_true($message->level === 'WARNING', "Message level does not match; expected 'WARNING', got '{$message->level}'.");
        assert_true($message->context === $context, 'Message context do not match; expected ' . json_encode($context) . ", got " . json_encode($message->context) . ".");
        assert_true(abs(time() - $message->time->getTimestamp()) < 2, 'Message time does not match current time; expected roughly ' . time() . ", got {$message->time->getTimestamp()}.");
        assert_true($message->time->getTimezone()->getName() === 'UTC', "Message timezone is not UTC; got '{$message->time->getTimezone()->getName()}'.");
    }
);
