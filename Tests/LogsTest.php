<?php

use PhpRepos\Logger\Core\Data\Message;
use function PhpRepos\Logger\API\Config\get_default_media;
use function PhpRepos\Logger\API\Config\set_default_media;
use function PhpRepos\TestRunner\Assertions\assert_false;
use function PhpRepos\TestRunner\Assertions\assert_true;
use function PhpRepos\TestRunner\Runner\test;
use function PhpRepos\Logger\API\Logs\log;

test(
    title: 'it should set and get default media',
    case: function () {
        assert_true(is_callable(get_default_media()[0]), 'Default media should be system_log');
        $media = fn (Message $message) => $message;
        assert_true(set_default_media($media)[0] === $media);
    }
);

test(
    title: 'it should send a log to the default media',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message;
        };

        set_default_media($media);

        log('hello world', 'INFO');

        assert_true($result->text === 'hello world', 'Log to media is wrong: ' . print_r($result, true));
    }
);

test(
    title: 'it should send a log to the given media',
    case: function () {
        $result = null;
        $media = function (Message $message) use (&$result) {
            $result = $message->text . ' from media';
        };

        log('hello world', 'INFO', [], $media);

        assert_true($result === 'hello world from media', 'Log to given media is wrong: ' . print_r($result, true));
    }
);

test(
    title: 'it should send a log to multiple media',
    case: function () {
        $result1 = null;
        $media1 = function (Message $message) use (&$result1) {
            $result1 = $message->text . ' from media1';
        };

        $result2 = null;
        $media2 = function (Message $message) use (&$result2) {
            $result2 = $message->text . ' from media2';
        };

        log('hello world', 'INFO', [], $media1, $media2);

        assert_true($result1 === 'hello world from media1', 'Log to given media1 is wrong: ' . print_r($result1, true));
        assert_true($result2 === 'hello world from media2', 'Log to given media2 is wrong: ' . print_r($result2, true));
    }
);

