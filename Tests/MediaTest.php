<?php

use PhpRepos\Logger\Log\Message;
use function PhpRepos\Logger\Media\file_lock;
use function PhpRepos\Logger\Media\file_put;
use function PhpRepos\TestRunner\Assertions\assert_false;
use function PhpRepos\TestRunner\Assertions\assert_true;
use function PhpRepos\TestRunner\Runner\test;

test(
    title: 'it should put the message to a file',
    case: function () {
        $media = file_put('log-put.txt');

        $message = Message::info('hello log');
        $media($message);
        assert_true(str_contains(file_get_contents('log-put.txt'), 'hello log'), 'Can not find the message in the log!');

        $message = Message::info('world log');
        $media($message);
        assert_true(str_contains(file_get_contents('log-put.txt'), 'hello log'), 'Can not find hello in the log!');
        assert_true(str_contains(file_get_contents('log-put.txt'), 'world log'), 'Can not find world in the log!');
    },
    after: function () {
        unlink('log-put.txt');
    }
);

test(
    title: 'it should put the message to a file using lock',
    case: function () {
        $media = file_lock('log-lock.txt');

        $message = Message::info('hello log');
        $media($message);
        assert_true(str_contains(file_get_contents('log-lock.txt'), 'hello log'), 'Can not find the message in the log!');

        $message = Message::info('world log');
        $media($message);
        assert_true(str_contains(file_get_contents('log-lock.txt'), 'hello log'), 'Can not find hello in the log!');
        assert_true(str_contains(file_get_contents('log-lock.txt'), 'world log'), 'Can not find world in the log!');
    },
    after: function () {
        unlink('log-lock.txt');
    }
);