<?php
declare(strict_types=1);

namespace Pfazzi\Session\Tests\DynamoDb;

use Aws\Sdk;
use Pfazzi\Session\DynamoDb\DynamoDbSessionHandler;
use PHPUnit\Framework\TestCase;

class DynamoDbSessionHandlerTest extends TestCase
{
    private DynamoDbSessionHandler $instance;

    protected function setUp(): void
    {
        $sdk = new Sdk([
            'region'   => 'eu-central-1',
            'version'  => 'latest'
        ]);

        $dynamodb = $sdk->createDynamoDb();

        $this->instance = new DynamoDbSessionHandler(
            $dynamodb,
            'session-handler-test'
        );
    }

    public function test_close()
    {
        self::assertTrue($this->instance->close());
    }

    public function test_gc()
    {
        self::assertTrue($this->instance->gc(1));
    }

    public function test_write_and_read()
    {
        self::assertTrue($this->instance->write('test456', 'test'));
        self::assertEquals('test', $this->instance->read('test456'));
    }

    public function testDoDestroy()
    {
        $this->instance->write('test456', 'test');
        $this->instance->destroy('test456');

        self::assertEquals('', $this->instance->read('test456'));
    }
}