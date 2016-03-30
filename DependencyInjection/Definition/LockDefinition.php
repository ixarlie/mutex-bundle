<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class LockDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
abstract class LockDefinition
{
    /**
     * @var string
     */
    private $type;

    /**
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @param array            $config
     * @param Definition       $service
     * @param ContainerBuilder $container
     */
    abstract public function configure(array $config, Definition $service, ContainerBuilder $container);

    /**
     * @param array            $config
     * @param Definition       $definition
     * @param ContainerBuilder $container
     */
    protected function addLoggerService(array $config, Definition $definition, ContainerBuilder $container)
    {
        if (isset($config['logger'])) {
            $definition->addArgument($container->findDefinition($config['logger']));
        }
    }
}
