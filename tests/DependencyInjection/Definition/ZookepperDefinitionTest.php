<?php declare(strict_types=1);

namespace Tests\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\ZookepperDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\ZookeeperStore;
use Tests\DependencyInjection\Definition\StoreDefinitionTestCase;

/**
 * Class ZookepperDefinitionTest
 */
final class ZookepperDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritDoc
     */
    protected function getClassName(): string
    {
        return ZookeeperStore::class;
    }

    /**
     * @inheritDoc
     */
    protected function getDefinitionInstance(): LockDefinition
    {
        return new ZookepperDefinition();
    }

    /**
     * @inheritDoc
     */
    protected function getDefinitionName(): string
    {
        return 'zookeeper';
    }

    /**
     * @inheritDoc
     */
    protected function assertStore(Definition $definition, array $configuration): void
    {
        static::assertCount(1, $definition->getArguments());
        static::assertEquals('my_client', $definition->getArgument(0));
    }

    /**
     * @inheritDoc
     */
    public function dataDefinitionProvider(): \Generator
    {
        yield [
            [
                'default' => 'foo.bar',
                'client'  => 'my_client',
            ],
        ];
        yield [
            [
                'default' => 'foo.bar',
                'client'  => 'my_client',
            ],
        ];
        yield [
            [
                'default' => 'foo.bar',
                'client'  => 'my_client',
                'logger'  => 'monolog.logger',
            ],
        ];
        yield [
            [
                'default'  => 'foo.bar',
                'client'   => 'my_client',
                'blocking' => [
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
                    'client' => 'my_service',
                ],
            ],
            [
                'foo' => [
                    'client' => 'my_service',
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'client' => 'my_service',
                    'logger' => 'monolog.logger',
                ],
            ],
            [
                'foo' => [
                    'client' => 'my_service',
                    'logger' => 'monolog.logger',
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'client'   => 'my_service',
                    'blocking' => [],
                ],
            ],
            [
                'foo' => [
                    'client'   => 'my_service',
                    'blocking' => [
                        'retry_sleep' => 100,
                        'retry_count' => PHP_INT_MAX,
                    ],
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'client'   => 'my_service',
                    'blocking' => [
                        'retry_sleep' => 200,
                        'retry_count' => 3,
                    ],
                ],
            ],
            [
                'foo' => [
                    'client'   => 'my_service',
                    'blocking' => [
                        'retry_sleep' => 200,
                        'retry_count' => 3,
                    ],
                ],
            ],
        ];
    }
}
