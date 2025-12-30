<?php

use PhpRepos\Logger\Core\Data\Message;
use function PhpRepos\Logger\API\Logs\emergency;
use function PhpRepos\Logger\API\Logs\alert;
use function PhpRepos\Logger\API\Logs\critical;
use function PhpRepos\Logger\API\Logs\error;
use function PhpRepos\Logger\API\Logs\warning;
use function PhpRepos\Logger\API\Logs\notice;
use function PhpRepos\Logger\API\Logs\info;
use function PhpRepos\Logger\API\Logs\debug;
use function PhpRepos\TestRunner\Assertions\assert_true;
use function PhpRepos\TestRunner\Runner\test;

test(
    title: 'it should log emergency messages',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        emergency('System is unusable', [], $media);

        assert_true($result->text === 'System is unusable', 'Emergency message text is wrong');
        assert_true($result->level === 'EMERGENCY', 'Emergency level is wrong: ' . $result->level);
    }
);

test(
    title: 'it should log alert messages',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        alert('Action must be taken immediately', [], $media);

        assert_true($result->text === 'Action must be taken immediately', 'Alert message text is wrong');
        assert_true($result->level === 'ALERT', 'Alert level is wrong: ' . $result->level);
    }
);

test(
    title: 'it should log critical messages',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        critical('Critical conditions', [], $media);

        assert_true($result->text === 'Critical conditions', 'Critical message text is wrong');
        assert_true($result->level === 'CRITICAL', 'Critical level is wrong: ' . $result->level);
    }
);

test(
    title: 'it should log error messages',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        error('Error conditions', [], $media);

        assert_true($result->text === 'Error conditions', 'Error message text is wrong');
        assert_true($result->level === 'ERROR', 'Error level is wrong: ' . $result->level);
    }
);

test(
    title: 'it should log warning messages',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        warning('Warning conditions', [], $media);

        assert_true($result->text === 'Warning conditions', 'Warning message text is wrong');
        assert_true($result->level === 'WARNING', 'Warning level is wrong: ' . $result->level);
    }
);

test(
    title: 'it should log notice messages',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        notice('Normal but significant condition', [], $media);

        assert_true($result->text === 'Normal but significant condition', 'Notice message text is wrong');
        assert_true($result->level === 'NOTICE', 'Notice level is wrong: ' . $result->level);
    }
);

test(
    title: 'it should log info messages',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        info('Informational messages', [], $media);

        assert_true($result->text === 'Informational messages', 'Info message text is wrong');
        assert_true($result->level === 'INFO', 'Info level is wrong: ' . $result->level);
    }
);

test(
    title: 'it should log debug messages',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        debug('Debug-level messages', [], $media);

        assert_true($result->text === 'Debug-level messages', 'Debug message text is wrong');
        assert_true($result->level === 'DEBUG', 'Debug level is wrong: ' . $result->level);
    }
);

test(
    title: 'it should include context in convenience functions',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        info('User action', ['user_id' => 123, 'action' => 'login'], $media);

        assert_true($result->text === 'User action', 'Message text is wrong');
        assert_true($result->context['user_id'] === 123, 'Context user_id is wrong');
        assert_true($result->context['action'] === 'login', 'Context action is wrong');
    }
);
