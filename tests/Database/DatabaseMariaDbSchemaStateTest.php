<?php

namespace Database;

use Generator;
use Illuminate\Database\MariaDbConnection;
use Illuminate\Database\Schema\MariaDbSchemaState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class DatabaseMariaDbSchemaStateTest extends TestCase
{
    #[DataProvider('provider')]
    public function testConnectionString(string $expectedConnectionString, array $expectedVariables, array $dbConfig): void
    {
        $connection = $this->createMock(MariaDbConnection::class);
        $connection->method('getConfig')->willReturn($dbConfig);

        $schemaState = new MariaDbSchemaState($connection);

        // test connectionString
        $method = new ReflectionMethod(get_class($schemaState), 'connectionString');
        $connString = $method->invoke($schemaState);

        self::assertEquals($expectedConnectionString, $connString);

        // test baseVariables
        $method = new ReflectionMethod(get_class($schemaState), 'baseVariables');
        $variables = $method->invoke($schemaState, $dbConfig);

        self::assertEquals($expectedVariables, $variables);
    }

    public static function provider(): Generator
    {
        yield 'default' => [
            ' --user="${:KENOURA_LOAD_USER}" --password="${:KENOURA_LOAD_PASSWORD}" --host="${:KENOURA_LOAD_HOST}" --port="${:KENOURA_LOAD_PORT}"', [
                'KENOURA_LOAD_SOCKET' => '',
                'KENOURA_LOAD_HOST' => '127.0.0.1',
                'KENOURA_LOAD_PORT' => '',
                'KENOURA_LOAD_USER' => 'root',
                'KENOURA_LOAD_PASSWORD' => '',
                'KENOURA_LOAD_DATABASE' => 'forge',
                'KENOURA_LOAD_SSL_CA' => '',
            ], [
                'username' => 'root',
                'host' => '127.0.0.1',
                'database' => 'forge',
            ],
        ];

        yield 'ssl_ca' => [
            ' --user="${:KENOURA_LOAD_USER}" --password="${:KENOURA_LOAD_PASSWORD}" --host="${:KENOURA_LOAD_HOST}" --port="${:KENOURA_LOAD_PORT}" --ssl-ca="${:KENOURA_LOAD_SSL_CA}"', [
                'KENOURA_LOAD_SOCKET' => '',
                'KENOURA_LOAD_HOST' => '',
                'KENOURA_LOAD_PORT' => '',
                'KENOURA_LOAD_USER' => 'root',
                'KENOURA_LOAD_PASSWORD' => '',
                'KENOURA_LOAD_DATABASE' => 'forge',
                'KENOURA_LOAD_SSL_CA' => 'ssl.ca',
            ], [
                'username' => 'root',
                'database' => 'forge',
                'options' => [
                    \PDO::MYSQL_ATTR_SSL_CA => 'ssl.ca',
                ],
            ],
        ];

        yield 'unix socket' => [
            ' --user="${:KENOURA_LOAD_USER}" --password="${:KENOURA_LOAD_PASSWORD}" --socket="${:KENOURA_LOAD_SOCKET}"', [
                'KENOURA_LOAD_SOCKET' => '/tmp/mysql.sock',
                'KENOURA_LOAD_HOST' => '',
                'KENOURA_LOAD_PORT' => '',
                'KENOURA_LOAD_USER' => 'root',
                'KENOURA_LOAD_PASSWORD' => '',
                'KENOURA_LOAD_DATABASE' => 'forge',
                'KENOURA_LOAD_SSL_CA' => '',
            ], [
                'username' => 'root',
                'database' => 'forge',
                'unix_socket' => '/tmp/mysql.sock',
            ],
        ];
    }
}
