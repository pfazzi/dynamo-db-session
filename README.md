# DynamoDB Session 

DynamoDB Session is a session handler that stores session data in DynamoDB.

## Installation

Use the package manager [composer](https://getcomposer.org/) to install DynamoDB Session.

```bash
composer require pfazzi/dynamo-db-session
```

## Usage

Define a proper service in `config/services.yaml`
```yaml
services:
    pfazzi.dynamo_db_session:
      class: Pfazzi\Session\DynamoDb\DynamoDbSessionHandler
      arguments:
        $tableName: 'dashboard-session-dev' # TODO: change me!
```
A DynamoDb table needs to exist in the configured region with the given `$tableName` option.
The primary key of the table must be a String with key "id".

Tell Symfony to use it as session handler in `config/packages/framework.yaml`:
```yaml
framework:
    session:
        handler_id: pfazzi.dynamo_db_session
```

### Create session table programmatically

You can programmatically create the session table using `Pfazzi\Session\DynamoDb\SessionTable` class, as in the following example:
```php
$tableName = 'some-session-table-name';

$sdk = new Aws\Sdk([
    'region'   => 'eu-central-1',
    'version'  => 'latest',
]);

$dynamodb = $sdk->createDynamoDb();

$sessionTable = new Pfazzi\Session\DynamoDb\SessionTable($dynamodb, $tableName);
$sessionTable->create();
```

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
[MIT](https://choosealicense.com/licenses/mit/)
