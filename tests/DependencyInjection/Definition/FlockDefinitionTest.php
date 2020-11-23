<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\FlockStore;

/**
 * Class FlockDefinitionTest
 */
final class FlockDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName(): string
    {
        return FlockStore::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance(): LockDefinition
    {
        return new FlockDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName(): string
    {
        return 'flock';
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration): void
    {
        static::assertCount(1, $definition->getArguments());
        $path = $definition->getArgument(0);

        static::assertIsString($path);
    }

    /**
     * @inheritdoc
     */
    public function dataDefinitionProvider(): \Generator
    {
        yield [
            [
                'default'  => 'foo.bar',
                'lock_dir' => '/tmp/locks',
            ],
        ];
        yield [
            [
                'default'  => 'foo.bar',
                'lock_dir' => '/tmp/locks',
                'logger'   => 'monolog.logger',
            ],
        ];
        yield [
            [
                'default'  => 'flock.default',
                'lock_dir' => '/tmp/locks',
                'logger'   => 'monolog.logger',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function dataConfigurationProvider(): \Generator
    {
        yield [
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                ],
            ],
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                    'logger'   => 'monolog.logger',
                ],
            ],
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                    'logger'   => 'monolog.logger',
                ],
            ],
        ];
    }
}
