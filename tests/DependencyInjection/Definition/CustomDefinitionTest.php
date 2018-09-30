<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\CustomDefinition;
use IXarlie\MutexBundle\Store\CustomStore;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class CustomDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName()
    {
        return CustomStore::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance()
    {
        return new CustomDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName()
    {
        return 'custom';
    }

    /**
     * @inheritdoc
     */
    protected function preConfigureContainer(ContainerBuilder $container, $name, array $configuration)
    {
        $definition = new Definition('%app.service.class%');

        $container->setDefinition($configuration['service'], $definition);
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration)
    {
        // custom does not have assertions.
        static::assertCount(1, $definition->getArguments());
        static::assertInstanceOf(Reference::class, $definition->getArgument(0));
        static::assertEquals($configuration['service'], (string) $definition->getArgument(0));
    }

    /**
     * @inheritdoc
     */
    public function getDefinitionProvider()
    {
        yield [
            [
                'default' => 'foo.bar',
                'service' => 'my_service',
            ]
        ];
        yield [
            [
                'default' => 'foo.bar',
                'service' => 'my_service',
                'logger'  => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default' => 'flock.default',
                'service' => 'my_service',
                'logger'  => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'  => 'flock.default',
                'service'  => 'my_service',
                'logger'   => 'monolog.logger',
                'blocking' => [
                    'retry_count' => 500,
                    'retry_sleep' => 1000,
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getConfigurationProvider()
    {
        yield [
            [
                'foo' => [
                    'service' => 'my_store_service'
                ]
            ],
            [
                'foo' => [
                    'service' => 'my_store_service'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'service' => 'my_store_service',
                    'logger'  => 'monolog.logger'
                ]
            ],
            [
                'foo' => [
                    'service' => 'my_store_service',
                    'logger'  => 'monolog.logger'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'service'  => 'my_store_service',
                    'blocking' => []
                ]
            ],
            [
                'foo' => [
                    'service'  => 'my_store_service',
                    'blocking' => [
                        'retry_sleep' => 100,
                        'retry_count' => PHP_INT_MAX,
                    ]
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'service'  => 'my_store_service',
                    'blocking' => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ],
            [
                'foo' => [
                    'service'  => 'my_store_service',
                    'blocking' => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ]
        ];
    }
}
