<?php

declare(strict_types=1);

namespace Pfazzi\Session\DynamoDb;

use Aws\DynamoDb\DynamoDbClient;

class SessionTable
{
    private DynamoDbClient $dynamodb;
    private string $tableName;

    public function __construct(DynamoDbClient $dynamodb, string $tableName)
    {
        $this->dynamodb  = $dynamodb;
        $this->tableName = $tableName;
    }

    public function create(): void
    {
        $this->dynamodb->createTable([
            'TableName' => $this->tableName,
            'BillingMode' => 'PAY_PER_REQUEST',
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'id',
                    'AttributeType' => 'S',
                ],
            ],
            'KeySchema' => [
                [
                    'AttributeName' => 'id',
                    'KeyType' => 'HASH',
                ],
            ],
        ]);
    }

    public function delete(): void
    {
        $this->dynamodb->deleteTable(['TableName' => $this->tableName]);
    }
}
