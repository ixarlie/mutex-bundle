<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Compiler\LockerPass;
use IXarlie\MutexBundle\DependencyInjection\IXarlieMutexExtension;
use IXarlie\MutexBundle\Model\LockerManagerInterface;
use NinjaMutex\Lock\FlockLock;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Class IXarlieMutexBundleTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexBundleTest extends \PHPUnit_Framework_TestCase
{
    private function getContainer()
    {
        return new ContainerBuilder(new ParameterBag(array(
            'kernel.debug'       => false,
            'kernel.bundles'     => [],
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__.'/../../' // src dir
        )));
    }

    public function testBundle()
    {
        $container = $this->getContainer();
        $loader = new IXarlieMutexExtension();
        $loader->load(
            [
                [
                    'flock' => [
                        'cache_dir' => '%kernel.cache_dir%'
                    ],
                    'redis' => [
                        'host' => '127.0.0.1',
                        'port' => 6379
                    ]
                ]
            ], $container);

        $pass = new LockerPass();
        $pass->process($container);

        $manager = $container->get('i_xarlie_mutex.locker_flock');
        $this->assertInstanceOf(LockerManagerInterface::class, $manager);

        $refl = new \ReflectionClass($manager);
        $prop = $refl->getProperty('locker');
        $prop->setAccessible(true);

        $locker = $prop->getValue($manager);
        $this->assertInstanceOf(FlockLock::class, $locker);
    }
}
