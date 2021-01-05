<?php

declare(strict_types=1);

namespace Pfazzi\Session\Tests\DynamoDb;

use Aws\Sdk;
use Pfazzi\Session\DynamoDb\DynamoDbSessionHandler;
use Pfazzi\Session\DynamoDb\SessionTable;
use PHPUnit\Framework\TestCase;

class DynamoDbSessionHandlerTest extends TestCase
{
    private DynamoDbSessionHandler $instance;
    private SessionTable $sessionTable;

    protected function setUp(): void
    {
        $tableName = 'session-handler-test';

        $sdk = new Sdk([
            'endpoint' => 'http://localhost:8000',
            'region'   => 'eu-central-1',
            'version'  => 'latest',
            'credentials' => [
                'key'    => 'my-access-key-id',
                'secret' => 'my-secret-access-key',
            ],
        ]);

        $dynamodb = $sdk->createDynamoDb();

        $this->sessionTable = new SessionTable($dynamodb, $tableName);
        $this->sessionTable->create();

        $this->instance = new DynamoDbSessionHandler($dynamodb, $tableName);
    }

    protected function tearDown(): void
    {
        $this->sessionTable->delete();
    }

    public function test_close(): void
    {
        self::assertTrue($this->instance->close());
    }

    public function test_gc(): void
    {
        self::assertTrue($this->instance->gc(1));
    }

    public function test_write_and_read(): void
    {
        self::assertTrue($this->instance->write('test456', 'test'));
        self::assertEquals('test', $this->instance->read('test456'));
    }

    public function test_rewrite_and_read(): void
    {
        self::assertTrue($this->instance->write('test456', 'test'));
        self::assertTrue($this->instance->write('test456', 'test test'));
        self::assertEquals('test test', $this->instance->read('test456'));
    }

    public function test_destroy(): void
    {
        $this->instance->write('test456', 'test');
        $this->instance->destroy('test456');

        self::assertEquals('', $this->instance->read('test456'));
    }
}
