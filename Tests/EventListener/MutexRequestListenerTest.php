<?php

namespace IXarlie\MutexBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use IXarlie\MutexBundle\Tests\Fixtures\DemoController;
use IXarlie\MutexBundle\EventListener\MutexRequestListener;
use IXarlie\MutexBundle\Manager\LockerManager;
use IXarlie\MutexBundle\Tests\Fixtures\ArrayLock;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class MutexRequestListenerTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;

    const DEFAULT_LOCKER = 'i_xarlie_mutex.locker';

    private function setUpListener(MutexRequestListener $listener, LockerManager $manager)
    {
        $listener->addLockerManager(self::DEFAULT_LOCKER, $manager);
        $listener->addLockerManager(self::DEFAULT_LOCKER.'_array', $manager);
    }

    /**
     * Queue waits for another process that lock the mutex previously.
     * It's recommended set a timeout in case the other request take so long, also if it was not specified the listener
     * will attempt a number of tries. After all of this if the new process cannot acquire the mutex a http exception
     * is thrown.
     *
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testQueueController()
    {
        $locker      = new ArrayLock();
        $listener1   = $this->getListener();
        $manager1    = new LockerManager($locker);
        $hashLocker1 = MutexRequestListener::generateLockName(DemoController::class, 'queueAction', '/');
        $event1      = $this->buildFilterEvent('queue');

        $this->setUpListener($listener1, $manager1);

        $listener1->onKernelController($event1);
        $this->assertTrue($manager1->isLocked($hashLocker1));
        $this->assertTrue($locker->isLocked($hashLocker1));
        $this->assertMutexCounters($manager1, $hashLocker1, 1);

        // Mutex will be locked until the controller finish or the process ends.
        // In an a real scenario we need a second locker manager but we keep the same locker instance.
        // Figure out our ArrayCache is a shared place to store mutex.
        $listener2   = $this->getListener();
        $manager2    = new LockerManager($locker);
        $event2      = $this->buildFilterEvent('queue');

        $this->setUpListener($listener2, $manager2);
        // Listener will throw an exception because the other process don't release the mutex after 2 waits of 10 sec.
        $listener2->onKernelController($event2);
    }

    /**
     * Block checks first if the resource if free, in that case block the resource for new requests.
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testBlockController()
    {
        $listener   = $this->getListener();
        $locker     = new ArrayLock();
        $manager    = new LockerManager($locker);
        $hashLocker = MutexRequestListener::generateLockName(DemoController::class, 'blockAction', '/');

        $this->setUpListener($listener, $manager);
        $event = $this->buildFilterEvent('block');

        $listener->onKernelController($event);
        $this->assertTrue($manager->isLocked($hashLocker));
        $this->assertTrue($locker->isLocked($hashLocker));
        $this->assertMutexCounters($manager, $hashLocker, 1);

        // Mutex will be raise an http exception when try to call endpoint
        $event2 = $this->buildFilterEvent('block');
        $listener->onKernelController($event2);
    }

    /**
     * Check raise an exception in case the resource is already locked but it's not going to acquire it.
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testCheckController()
    {
        $listener   = $this->getListener();
        $locker     = new ArrayLock();
        $manager    = new LockerManager($locker);
        $hashLocker = MutexRequestListener::generateLockName(DemoController::class, 'checkAction', '/');

        $this->setUpListener($listener, $manager);
        $event = $this->buildFilterEvent('check');

        $listener->onKernelController($event);
        $this->assertFalse($manager->isLocked($hashLocker));
        $this->assertFalse($locker->isLocked($hashLocker));
        $this->assertMutexCounters($manager, $hashLocker, 0);

        // Acquire manually the resource
        $manager->acquireLock($hashLocker);
        $this->assertMutexCounters($manager, $hashLocker, 1);

        // Mutex will be raise an http exception when try to call endpoint
        $event2 = $this->buildFilterEvent('check');
        $listener->onKernelController($event2);
    }

    /**
     * Force checks if the resource is locked, in that case release the mutex and acquire it.
     */
    public function testForceController()
    {
        $listener   = $this->getListener();
        $locker     = new ArrayLock();
        $manager    = new LockerManager($locker);
        $hashLocker = MutexRequestListener::generateLockName(DemoController::class, 'forceAction', '/');

        $this->setUpListener($listener, $manager);
        $event = $this->buildFilterEvent('force');

        // First time the resource is not locked
        $listener->onKernelController($event);
        $this->assertTrue($manager->isLocked($hashLocker));
        $this->assertTrue($locker->isLocked($hashLocker));
        $this->assertMutexCounters($manager, $hashLocker, 1);

        // Mutex will be raise an http exception when try to call endpoint
        $event2 = $this->buildFilterEvent('force');
        $listener->onKernelController($event2);
        $this->assertMutexCounters($manager, $hashLocker, 1);
    }

    public function testReplacePlaceholders()
    {
        $request = new Request();
        $request->attributes->set('_route_params', [
            'id'    => 1,
            'color' => 'red'
        ]);

        $name = 'resource_{id}_{color}';
        $name = MutexRequestListener::replacePlaceholders($request, $name);

        $this->assertEquals('resource_1_red', $name);
    }

    public function testReplaceNoPlaceholder()
    {
        $request = new Request();
        $request->attributes->set('_route_params', [
            'id'    => 1,
            'color' => 'red'
        ]);

        $name = 'resource';
        $name = MutexRequestListener::replacePlaceholders($request, $name);

        $this->assertEquals('resource', $name);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testReplaceMissingPlaceholder()
    {
        $request = new Request();
        $request->attributes->set('_route_params', ['id' => 1]);

        $name = 'resource_{id}_{color}';
        MutexRequestListener::replacePlaceholders($request, $name);
    }

    private function assertMutexCounters(LockerManagerInterface $manager, $name, $counter)
    {
        $refClass = new \ReflectionClass(LockerManager::class);
        $refProp  = $refClass->getProperty('locks');
        $refProp->setAccessible(true);
        $values   = $refProp->getValue($manager);

        $this->assertArrayHasKey($name, $values);
        $mutex = $values[$name];
        $refClass = new \ReflectionClass(\NinjaMutex\Mutex::class);
        $refProp  = $refClass->getProperty('counter');
        $refProp->setAccessible(true);
        $value    = $refProp->getValue($mutex);

        $this->assertEquals($counter, $value);
    }

    /**
     * @return MutexRequestListener
     */
    private function getListener()
    {
        $reader   = new AnnotationReader();
        $listener = new MutexRequestListener($reader);
        // Set as queue max timeout 5 seconds and 2 tries
        $listener->setMaxQueueTimeout(5);
        $listener->setMaxQueueTry(2);

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
