<?php

namespace IXarlie\MutexBundle\Tests;

use IXarlie\MutexBundle\IXarlieMutexBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use IXarlie\MutexBundle\Tests\Fixtures\AppKernel;

/**
 * Class IXarlieMutexBundleTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $bundle = new IXarlieMutexBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }
    
    public function testBootBundle()
    {
        $kernel = new AppKernel('test', true);
        // Boot, if something is wrong boot will complain
        $kernel->boot();
        
        $this->assertTrue(true);
    }
}
