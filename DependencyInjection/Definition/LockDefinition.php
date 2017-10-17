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
     * @param Definition       $manager
     * @param ContainerBuilder $container
     */
    public function configure(array $config, Definition $manager, ContainerBuilder $container)
    {
        // Get the locker definition.
        $locker = $this->getLocker($config, $container);
        
        // If the locker have a client service, allow configure it and add it to the locker.
        if ($client = $this->getClient($config, $container)) {
            $client->setPublic(false);
            $this->configureClient($locker, $client);
        }
        
        // LockerManager first argument is a \NinjaMutex\Lock\LockInterface definition.
        $manager->addArgument($locker);
        if (isset($config['logger'])) {
            // If a logger is configured, add it as argument
            $manager->addArgument($container->findDefinition($config['logger']));
        }
    }

    /**
     * Create a locker definition that will be use in the LockerManagerInterface
     *
     * @param array $config
     * @param ContainerBuilder $container
     *
     * @return Definition
     */
    abstract protected function getLocker(array $config, ContainerBuilder $container);

    /**
     * Create a client definition that will be use in the locker
     *
     * @param array $config
     * @param ContainerBuilder $container
     *
     * @return Definition
     */
    abstract protected function getClient(array $config, ContainerBuilder $container);

    /**
     * Configure client
     *
     * @param Definition $locker
     * @param Definition $client
     */
    protected function configureClient(Definition $locker, Definition $client)
    {
        $locker->addArgument($client);
    }
}
