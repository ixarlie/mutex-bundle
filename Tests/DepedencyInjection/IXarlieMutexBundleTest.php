<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Compiler\LockerPass;
use IXarlie\MutexBundle\DependencyInjection\IXarlieMutexExtension;
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
        $this->markTestSkipped('@TODO');
        $container = $this->getContainer();
        $loader = new IXarlieMutexExtension();
        $loader->load(
            [
                [
                    'flock' => [
                        'cache_dir' => '%kernel.cache_dir'
                    ]
                ]
            ], $container);

        $pass = new LockerPass();
        $pass->process($container);

        // @TODO check parameters and services
    }
}