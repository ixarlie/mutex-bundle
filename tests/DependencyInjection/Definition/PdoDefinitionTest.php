<?php declare(strict_types=1);

namespace Tests\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\PdoDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\PdoStore;
use Tests\DependencyInjection\Definition\StoreDefinitionTestCase;

/**
 * Class PdoDefinitionTest
 */
final class PdoDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritDoc
     */
    protected function getClassName(): string
    {
        return PdoStore::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDefinitionInstance(): LockDefinition
    {
        return new PdoDefinition();
    }

    /**
     * @inheritDoc
     */
    protected function getDefinitionName(): string
    {
        return 'pdo';
    }

    /**
     * @inheritDoc
     */
    protected function assertStore(Definition $definition, array $configuration): void
    {
        static::assertCount(2, $definition->getArguments());
        static::assertSame($configuration['dsn'], $definition->getArgument(0));

        unset($configuration['dsn'], $configuration['logger'], $configuration['blocking'], $configuration['default']);
        static::assertSame($configuration, $definition->getArgument(1));
    }

    /**
     * @inheritDoc
     */
    public function dataDefinitionProvider(): \Generator
    {
        yield [
            [
                'default' => 'foo.bar',
                'dsn'     => 'mysql:host=127.0.0.1;dbname=lock',
            ],
        ];
        yield [
            [
                'default'  => 'foo.bar',
                'dsn'      => 'mysql:host=127.0.0.1;dbname=lock',
                'db_table' => 'my_table',
            ],
        ];
        yield [
            [
                'default'   => 'foo.bar',
                'dsn'       => 'mysql:host=127.0.0.1;dbname=lock',
                'db_table'  => 'my_table',
                'db_id_col' => 'my_id_col',
            ],
        ];
        yield [
            [
                'default'      => 'foo.bar',
                'dsn'          => 'mysql:host=127.0.0.1;dbname=lock',
                'db_table'     => 'my_table',
                'db_id_col'    => 'my_id_col',
                'db_token_col' => 'my_token_col',
            ],
        ];
        yield [
            [
                'default'           => 'foo.bar',
                'dsn'               => 'mysql:host=127.0.0.1;dbname=lock',
                'db_table'          => 'my_table',
                'db_id_col'         => 'my_id_col',
                'db_token_col'      => 'my_token_col',
                'db_expiration_col' => 'my_expiration_col',
            ],
        ];
        yield [
            [
                'default'           => 'foo.bar',
                'dsn'               => 'mysql:host=127.0.0.1;dbname=lock',
                'db_table'          => 'my_table',
                'db_id_col'         => 'my_id_col',
                'db_token_col'      => 'my_token_col',
                'db_expiration_col' => 'my_expiration_col',
                'logger'            => 'monolog.logger',
            ],
        ];
        yield [
            [
                'default'           => 'foo.bar',
                'dsn'               => 'mysql:host=127.0.0.1;dbname=lock',
                'db_table'          => 'my_table',
                'db_id_col'         => 'my_id_col',
                'db_token_col'      => 'my_token_col',
                'db_expiration_col' => 'my_expiration_col',
                'blocking'          => [
                    'retry_count' => 500,
                    'retry_sleep' => 1000,
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function dataConfigurationProvider(): \Generator
    {
        yield [
            [
                'foo' => [
                    'dsn' => 'mysql:host=127.0.0.1;dbname=lock',
                ],
            ],
            [
                'foo' => [
                    'dsn'                   => 'mysql:host=127.0.0.1;dbname=lock',
                    'db_table'              => 'lock_keys',
                    'db_id_col'             => 'key_id',
                    'db_token_col'          => 'key_token',
                    'db_expiration_col'     => 'key_expiration',
                    'db_connection_options' => [],
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'dsn'                   => 'mysql:host=127.0.0.1;dbname=lock',
                    'db_table'              => 'table',
                    'db_id_col'             => 'id',
                    'db_token_col'          => 'token',
                    'db_expiration_col'     => 'expiration',
                    'db_connection_options' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
            [
                'foo' => [
                    'dsn'                   => 'mysql:host=127.0.0.1;dbname=lock',
                    'db_table'              => 'table',
                    'db_id_col'             => 'id',
                    'db_token_col'          => 'token',
                    'db_expiration_col'     => 'expiration',
                    'db_connection_options' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'dsn'      => 'mysql:host=127.0.0.1;dbname=lock',
                    'blocking' => [],
                ],
            ],
            [
                'foo' => [
                    'dsn'                   => 'mysql:host=127.0.0.1;dbname=lock',
                    'db_table'              => 'lock_keys',
                    'db_id_col'             => 'key_id',
                    'db_token_col'          => 'key_token',
                    'db_expiration_col'     => 'key_expiration',
                    'db_connection_options' => [],
                    'blocking'              => [
                        'retry_sleep' => 100,
                        'retry_count' => PHP_INT_MAX,
                    ],
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'dsn'      => 'mysql:host=127.0.0.1;dbname=lock',
                    'blocking' => [
                        'retry_sleep' => 200,
                        'retry_count' => 3,
                    ],
                ],
            ],
            [
                'foo' => [
                    'dsn'                   => 'mysql:host=127.0.0.1;dbname=lock',
                    'db_table'              => 'lock_keys',
                    'db_id_col'             => 'key_id',
                    'db_token_col'          => 'key_token',
                    'db_expiration_col'     => 'key_expiration',
                    'db_connection_options' => [],
                    'blocking'              => [
                        'retry_sleep' => 200,
                        'retry_count' => 3,
                    ],
                ],
            ],
        ];
    }
}
