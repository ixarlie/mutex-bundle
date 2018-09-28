<?php

namespace Tests\DepedencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\CombinedDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\CombinedStore;
use Symfony\Component\Lock\Strategy\ConsensusStrategy;

/**
 * Class CombinedDefinitionTest
 */
class CombinedDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance()
    {
        return new CombinedDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName()
    {
        return 'combined';
    }

    /**
     * @param Definition $definition
     * @param array      $configuration
     * @param array      $parameters
     */
    protected function assertStore(Definition $definition, array $configuration, array $parameters)
    {
        $e = 4;
    }

    /**
     * @return array
     */
    public function getConfigurationProvider()
    {
        return [
            [
                [
                    'default'  => 'foo.bar',
                    'stores'   => ['my_store_service_one', 'my_store_service_two'],
                    'strategy' => 'consensus',
                    'logger'   => 'monolog.logger'
                ],
                CombinedStore::class,
                ['strategyClass' => ConsensusStrategy::class]
            ]
        ];
    }
}
