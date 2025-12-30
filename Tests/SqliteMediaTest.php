<?php

use PhpRepos\Logger\Core\Data\Message;
use PhpRepos\Logger\Core\Exceptions\SqliteException;
use function PhpRepos\Logger\Core\Messages\create;
use function PhpRepos\Logger\API\Media\sqlite;
use function PhpRepos\Logger\API\Logs\info;
use function PhpRepos\TestRunner\Assertions\assert_true;
use function PhpRepos\TestRunner\Assertions\assert_false;
use function PhpRepos\TestRunner\Runner\test;

test(
    title: 'it should persist logs to file database',
    case: function () {
        $dbPath = 'test-logs.db';
        $media = sqlite($dbPath);

        // Write logs
        $message1 = create('INFO', 'Persistent log', ['type' => 'test']);
        $media($message1);

        // Verify database file was created
        assert_true(file_exists($dbPath), 'Database file was not created');

        // Verify we can read from the database
        $db = new PDO('sqlite:' . $dbPath);
        $result = $db->query('SELECT COUNT(*) as count FROM logs');
        $row = $result->fetch(PDO::FETCH_ASSOC);

        assert_true($row['count'] === 1, 'Expected 1 log entry, got ' . $row['count']);

        // Verify log content
        $result = $db->query("SELECT * FROM logs WHERE message = 'Persistent log'");
        $log = $result->fetch(PDO::FETCH_ASSOC);

        assert_true($log['level'] === 'INFO', 'Expected level INFO, got ' . $log['level']);
        assert_true($log['message'] === 'Persistent log', 'Message mismatch');

        $context = json_decode($log['context'], true);
        assert_true($context['type'] === 'test', 'Context mismatch');
    },
    after: function () {
        if (file_exists('test-logs.db')) {
            unlink('test-logs.db');
        }
    }
);

test(
    title: 'it should create database and table if they do not exist',
    case: function () {
        $dbPath = 'new-db-test.db';

        // Ensure database doesn't exist
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }

        $media = sqlite($dbPath);
        $message = create('DEBUG', 'Test message');
        $media($message);

        // Verify database was created
        assert_true(file_exists($dbPath), 'Database file was not created');

        // Verify table exists and has correct structure
        $db = new PDO('sqlite:' . $dbPath);
        $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='logs'");
        $table = $result->fetch(PDO::FETCH_ASSOC);

        assert_true($table['name'] === 'logs', 'Logs table was not created');

        // Verify columns exist
        $result = $db->query("PRAGMA table_info(logs)");
        $columns = [];
        while ($col = $result->fetch(PDO::FETCH_ASSOC)) {
            $columns[] = $col['name'];
        }

        assert_true(in_array('id', $columns), 'Missing id column');
        assert_true(in_array('level', $columns), 'Missing level column');
        assert_true(in_array('message', $columns), 'Missing message column');
        assert_true(in_array('context', $columns), 'Missing context column');
        assert_true(in_array('time', $columns), 'Missing time column');
    },
    after: function () {
        if (file_exists('new-db-test.db')) {
            unlink('new-db-test.db');
        }
    }
);

test(
    title: 'it should properly encode context as JSON',
    case: function () {
        $dbPath = 'context-test.db';
        $media = sqlite($dbPath);

        $complexContext = [
            'user' => 'john',
            'action' => 'login',
            'metadata' => [
                'ip' => '192.168.1.1',
                'browser' => 'Chrome'
            ]
        ];

        $message = create('INFO', 'User action', $complexContext);
        $media($message);

        // Verify context was properly stored
        $db = new PDO('sqlite:' . $dbPath);
        $result = $db->query("SELECT context FROM logs LIMIT 1");
        $log = $result->fetch(PDO::FETCH_ASSOC);

        $decodedContext = json_decode($log['context'], true);
        assert_true($decodedContext['user'] === 'john', 'User context mismatch');
        assert_true($decodedContext['metadata']['ip'] === '192.168.1.1', 'Nested context mismatch');
    },
    after: function () {
        if (file_exists('context-test.db')) {
            unlink('context-test.db');
        }
    }
);

test(
    title: 'it should throw exception immediately when database path is not writable',
    case: function () {
        // Create a regular file, then try to create a database "inside" it
        // This will fail on both Linux and Windows
        $regularFile = sys_get_temp_dir() . '/not-a-directory.txt';
        file_put_contents($regularFile, 'test content');

        $exceptionThrown = false;

        try {
            info('this should fail', [], sqlite($regularFile . '/cannot-create.db'));
            assert_false(true, 'Test should not reach this point - exception should have been thrown');
        } catch (SqliteException $e) {
            $exceptionThrown = true;
        }

        assert_true($exceptionThrown, 'SqliteException should have been thrown for invalid database path');
    },
    after: function () {
        $regularFile = sys_get_temp_dir() . '/not-a-directory.txt';
        if (file_exists($regularFile)) {
            unlink($regularFile);
        }
    }
);
