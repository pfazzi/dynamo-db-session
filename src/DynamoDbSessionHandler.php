<?php
declare(strict_types=1);

namespace Pfazzi\Session\DynamoDb;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;

class DynamoDbSessionHandler extends AbstractSessionHandler
{
    private DynamoDbClient $dynamodb;
    private string $tableName;
    private Marshaler $marshaler;

    public function __construct(DynamoDbClient $dynamodb, string $tableName)
    {
        $this->dynamodb = $dynamodb;
        $this->tableName = $tableName;

        $this->marshaler = new Marshaler();
    }

    public function close(): bool
    {
        return true;
    }

    public function gc($maxlifetime): bool
    {
        return true;
    }

    protected function doRead(string $sessionId): string
    {
        $key = $this->marshaler->marshalItem([
            'id' => $sessionId
        ]);

        $result = $this->dynamodb->getItem([
            'TableName' => $this->tableName,
            'Key' => $key
        ]);

        $item = $result["Item"];
        if (null === $item) {
            return '';
        }

        $item = $this->marshaler->unmarshalItem($result["Item"]);

        return $item['data'];
    }

    protected function doWrite(string $sessionId, string $data): bool
    {
        $item = $this->marshaler->marshalItem([
            'id' => $sessionId,
            'data' => $data,
        ]);

        $this->dynamodb->putItem([
            'TableName' => $this->tableName,
            'Item' => $item,
        ]);

        return true;
    }

    protected function doDestroy(string $sessionId): bool
    {
        $key = $this->marshaler->marshalItem([
            'id' => $sessionId
        ]);

        $this->dynamodb->deleteItem([
            'TableName' => $this->tableName,
            'Key' => $key
        ]);

        return true;
    }

    public function updateTimestamp($session_id, $session_data): bool
    {
        return true;
    }
}