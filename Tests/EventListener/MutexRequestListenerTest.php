<?php

namespace IXarlie\MutexBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use IXarlie\MutexBundle\Tests\Fixtures\DemoController;
use IXarlie\MutexBundle\EventListener\MutexRequestListener;
use IXarlie\MutexBundle\Manager\LockerManager;
use IXarlie\MutexBundle\Tests\Fixtures\ArrayLock;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use NinjaMutex\Mutex;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Class MutexRequestListenerTest.
 */
class MutexRequestListenerTest extends TestCase
{
    use UtilTestTrait;

    const DEFAULT_LOCKER = 'i_xarlie_mutex.locker';

    private function setUpListener(MutexRequestListener $listener, LockerManager $manager)
    {
        $listener->addLockerManager(self::DEFAULT_LOCKER, $manager);
        $listener->addLockerManager(self::DEFAULT_LOCKER . '_array', $manager);
    }

    /**
     * Queue waits for another process that lock the mutex previously.
     * It's recommended set a timeout in case the other request take so long, also if it was not specified the listener
     * will attempt a number of tries. After all of this if the new process cannot acquire the mutex a http exception
     * is thrown.
     */
    public function testQueueController()
    {
        static::expectException(HttpException::class);

        $locker      = new ArrayLock();
        $listener1   = $this->getListener();
        $manager1    = new LockerManager($locker);
        $hashLocker1 = MutexRequestListener::generateLockName(DemoController::class, 'queueAction', '/');
        $event1      = $this->buildFilterEvent('queue');

        $this->setUpListener($listener1, $manager1);

        $listener1->onKernelController($event1);
        static::assertTrue($manager1->isLocked($hashLocker1));
        static::assertTrue($locker->isLocked($hashLocker1));
        static::assertMutexCounters($manager1, $hashLocker1, 1);

        // Mutex will be locked until the controller finish or the process ends.
        // In an a real scenario we need a second locker manager but we keep the same locker instance.
        // Figure out our ArrayCache is a shared place to store mutex.
        $listener2 = $this->getListener();
        $manager2  = new LockerManager($locker);
        $event2    = $this->buildFilterEvent('queue');

        $this->setUpListener($listener2, $manager2);
        // Listener will throw an exception because the other process don't release the mutex after 2 waits of 10 sec.
        $listener2->onKernelController($event2);
    }

    /**
     * Block checks first if the resource if free, in that case block the resource for new requests.
     */
    public function testBlockController()
    {
        static::expectException(HttpException::class);

        $listener   = $this->getListener();
        $locker     = new ArrayLock();
        $manager    = new LockerManager($locker);
        $hashLocker = MutexRequestListener::generateLockName(DemoController::class, 'blockAction', '/');

        $this->setUpListener($listener, $manager);
        $event = $this->buildFilterEvent('block');

        $listener->onKernelController($event);
        static::assertTrue($manager->isLocked($hashLocker));
        static::assertTrue($locker->isLocked($hashLocker));
        static::assertMutexCounters($manager, $hashLocker, 1);

        // Mutex will be raise an http exception when try to call endpoint
        $event2 = $this->buildFilterEvent('block');
        $listener->onKernelController($event2);
    }

    /**
     * Check raise an exception in case the resource is already locked but it's not going to acquire it.
     */
    public function testCheckController()
    {
        static::expectException(HttpException::class);

        $listener   = $this->getListener();
        $locker     = new ArrayLock();
        $manager    = new LockerManager($locker);
        $hashLocker = MutexRequestListener::generateLockName(DemoController::class, 'checkAction', '/');

        $this->setUpListener($listener, $manager);
        $event = $this->buildFilterEvent('check');

        $listener->onKernelController($event);
        static::assertFalse($manager->isLocked($hashLocker));
        static::assertFalse($locker->isLocked($hashLocker));
        static::assertMutexCounters($manager, $hashLocker, 0);

        // Acquire manually the resource
        $manager->acquireLock($hashLocker);
        static::assertMutexCounters($manager, $hashLocker, 1);

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
        static::assertTrue($manager->isLocked($hashLocker));
        static::assertTrue($locker->isLocked($hashLocker));
        static::assertMutexCounters($manager, $hashLocker, 1);

        // Mutex will be raise an http exception when try to call endpoint
        $event2 = $this->buildFilterEvent('force');
        $listener->onKernelController($event2);
        static::assertMutexCounters($manager, $hashLocker, 1);
    }

    public function testReplacePlaceholders()
    {
        $request = new Request();
        $request->attributes->set('_route_params', [
            'id'    => 1,
            'color' => 'red',
        ]);

        $name = 'resource_{id}_{color}';
        $name = MutexRequestListener::replacePlaceholders($request, $name);

        static::assertEquals('resource_1_red', $name);
    }

    public function testReplaceNoPlaceholder()
    {
        $request = new Request();
        $request->attributes->set('_route_params', [
            'id'    => 1,
            'color' => 'red',
        ]);

        $name = 'resource';
        $name = MutexRequestListener::replacePlaceholders($request, $name);

        static::assertEquals('resource', $name);
    }

    public function testReplaceMissingPlaceholder()
    {
        static::expectException(\RuntimeException::class);

        $request = new Request();
        $request->attributes->set('_route_params', ['id' => 1]);

        $name = 'resource_{id}_{color}';
        MutexRequestListener::replacePlaceholders($request, $name);
    }

    /**
     * @param LockerManagerInterface $manager
     * @param string                 $name
     * @param int                    $counter
     */
    private static function assertMutexCounters(LockerManagerInterface $manager, string $name, int $counter)
    {
        $refClass = new \ReflectionClass(LockerManager::class);
        $refProp  = $refClass->getProperty('locks');
        $refProp->setAccessible(true);
        $values = $refProp->getValue($manager);

        static::assertArrayHasKey($name, $values);
        $mutex    = $values[$name];
        $refClass = new \ReflectionClass(Mutex::class);
        $refProp  = $refClass->getProperty('counter');
        $refProp->setAccessible(true);
        $value = $refProp->getValue($mutex);

        static::assertEquals($counter, $value);
    }

    /**
     * @return MutexRequestListener
     */
    private function getListener(): MutexRequestListener
    {
        $reader   = new AnnotationReader();
        $listener = new MutexRequestListener($reader);
        // Set as queue max timeout 5 seconds and 2 tries
        $listener->setMaxQueueTimeout(5);
        $listener->setMaxQueueTry(2);

        return $listener;
    }

    /**
     * @param string $action
     *
     * @return ControllerEvent
     */
    private function buildFilterEvent(string $action): ControllerEvent
    {
        $kernelMock = $this->createMock(HttpKernelInterface::class);

        return new ControllerEvent(
            $kernelMock,
            [new DemoController(), $action . 'Action'],
            Request::create('/'),
            HttpKernelInterface::MASTER_REQUEST
        );
    }
}
