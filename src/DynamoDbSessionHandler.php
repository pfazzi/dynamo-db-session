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

    /**
     * @psalm-param int $maxlifetime
     */
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

        $item = $result["Item"] ?? null;
        if (null === $item) {
            return '';
        }

        if (!is_array($item)) {
            throw new \LogicException(sprintf('Expected an array, %s found', gettype($result["Item"])));
        }

        $item = $this->marshaler->unmarshalItem($item);
        if (!is_array($item)) {
            throw new \LogicException(sprintf('Expected an array, %s found', gettype($result["Item"])));
        }

        $value = $item['data'] ?? null;

        if (!is_string($value)) {
            throw new \RuntimeException('Data field value is expected to be a string');
        }

        return $value;
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

    /**
     * @psalm-param string $session_id The session id
     * @psalm-param string $session_data
     */
    public function updateTimestamp($session_id, $session_data): bool
    {
        return true;
    }
}