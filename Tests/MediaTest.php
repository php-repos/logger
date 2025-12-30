<?php

use PhpRepos\Logger\Core\Data\Message;
use PhpRepos\Logger\Core\Exceptions\FileException;
use function PhpRepos\Logger\Core\Messages\create;
use function PhpRepos\Logger\API\Media\file_lock;
use function PhpRepos\Logger\API\Media\file_put;
use function PhpRepos\Logger\API\Media\system_log;
use function PhpRepos\Logger\API\Logs\info;
use function PhpRepos\TestRunner\Assertions\assert_false;
use function PhpRepos\TestRunner\Assertions\assert_true;
use function PhpRepos\TestRunner\Runner\test;

test(
    title: 'it should put the message to a file',
    case: function () {
        $media = file_put('log-put.txt');

        $message = create('INFO', 'hello log');
        $media($message);
        assert_true(str_contains(file_get_contents('log-put.txt'), 'hello log'), 'Can not find the message in the log!');

        $message = create('INFO', 'world log');
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

        $message = create('INFO', 'hello log');
        $media($message);
        assert_true(str_contains(file_get_contents('log-lock.txt'), 'hello log'), 'Can not find the message in the log!');

        $message = create('INFO', 'world log');
        $media($message);
        assert_true(str_contains(file_get_contents('log-lock.txt'), 'hello log'), 'Can not find hello in the log!');
        assert_true(str_contains(file_get_contents('log-lock.txt'), 'world log'), 'Can not find world in the log!');
    },
    after: function () {
        unlink('log-lock.txt');
    }
);

test(
    title: 'it should write to system log without errors',
    case: function () {
        $media = system_log();
        $message = create('INFO', 'test syslog message');

        // Just verify it doesn't throw an exception
        $media($message);
        assert_true(true, 'System log media executed successfully');
    }
);

test(
    title: 'it should throw exception immediately when file_put path is not writable',
    case: function () {
        // Create a regular file, then try to create a log file "inside" it
        // This will fail on both Linux and Windows
        $regularFile = sys_get_temp_dir() . '/not-a-directory-put.txt';
        file_put_contents($regularFile, 'test content');

        $exceptionThrown = false;

        try {
            info('this should fail', [], file_put($regularFile . '/cannot-create.log'));
            assert_false(true, 'Test should not reach this point - exception should have been thrown');
        } catch (FileException $e) {
            $exceptionThrown = true;
        }

        assert_true($exceptionThrown, 'FileException should have been thrown for invalid file path');
    },
    after: function () {
        $regularFile = sys_get_temp_dir() . '/not-a-directory-put.txt';
        if (file_exists($regularFile)) {
            unlink($regularFile);
        }
    }
);

test(
    title: 'it should throw exception immediately when file_lock path is not writable',
    case: function () {
        // Create a regular file, then try to create a log file "inside" it
        // This will fail on both Linux and Windows
        $regularFile = sys_get_temp_dir() . '/not-a-directory-lock.txt';
        file_put_contents($regularFile, 'test content');

        $exceptionThrown = false;

        try {
            info('this should fail', [], file_lock($regularFile . '/cannot-create.log'));
            assert_false(true, 'Test should not reach this point - exception should have been thrown');
        } catch (FileException $e) {
            $exceptionThrown = true;
        }

        assert_true($exceptionThrown, 'FileException should have been thrown for invalid file path');
    },
    after: function () {
        $regularFile = sys_get_temp_dir() . '/not-a-directory-lock.txt';
        if (file_exists($regularFile)) {
            unlink($regularFile);
        }
    }
);