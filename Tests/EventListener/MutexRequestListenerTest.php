<?php

namespace IXarlie\MutexBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use IXarlie\MutexBundle\Tests\Fixtures\DemoController;
use IXarlie\MutexBundle\EventListener\MutexRequestListener;
use IXarlie\MutexBundle\Manager\LockerManager;
use IXarlie\MutexBundle\Tests\Fixtures\ArrayLock;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MutexRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;
    
    const DEFAULT_LOCKER = 'i_xarlie_mutex.locker';
    
    public function testInstance()
    {
        $listener = $this->getListener();
        $this->assertInstanceOf(EventSubscriberInterface::class, $listener);
    }
    
    public function testQueueController()
    {
        $listener   = $this->getListener();
        $locker     = new ArrayLock();
        $manager    = new LockerManager($locker);
        $hashLocker = MutexRequestListener::generateLockName(DemoController::class, 'queueAction', '/');

        $listener->addLockerManager(self::DEFAULT_LOCKER, $manager);
        $listener->addLockerManager(self::DEFAULT_LOCKER.'_array', $manager);
        $event = $this->buildFilterEvent('queue');

        $listener->onKernelController($event);
        $this->assertTrue($manager->isLocked($hashLocker));
        $this->assertTrue($locker->isLocked($hashLocker));
        
        // Mutex will be locked until the controller finish or the process ends, we can check it out
        $event2 = $this->buildFilterEvent('queue');
        $listener->onKernelController($event2);
    }

    /**
     * @return MutexRequestListener
     */
    private function getListener()
    {
        $reader   = new AnnotationReader();
        $listener = new MutexRequestListener($reader);

        return $listener;
    }
    
    private function buildFilterEvent($action)
    {
        $kernelMock = $this->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $event = new FilterControllerEvent(
            $kernelMock,
            [new DemoController(), $action.'Action'],
            Request::create('/'),
            HttpKernelInterface::MASTER_REQUEST
        );
        
        return $event;
    }
}
