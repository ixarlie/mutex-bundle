<?php

namespace IXarlie\MutexBundle\Tests\EventListener;

use IXarlie\MutexBundle\EventListener\MutexRequestListener;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MutexRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;
    
    public function testInstance()
    {
        $listener = new MutexRequestListener();
        $this->assertInstanceOf(EventSubscriberInterface::class, $listener);
    }
    
    public function testKernelController()
    {
        // @TODO complete
        $this->assertTrue(true);
    }
}
