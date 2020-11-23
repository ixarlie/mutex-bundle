<?php

namespace Tests\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\EventListener\MutexRequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Tests\Fixtures\ArrayStore;

/**
 * Class MutexRequestListenerTest.
 */
final class MutexRequestListenerTest extends TestCase
{
    public function testOnKernelController(): void
    {
        $listener   = new MutexRequestListener();
        $store      = new ArrayStore();
        $factory    = new LockFactory($store);
        $annotation = new MutexRequest([
            'mode'    => 'block',
            'name'    => 'resource',
            'service' => 'array',
        ]);
        $event      = $this->createEvent($annotation);

        static::assertFalse($event->getRequest()->attributes->has('_ixarlie_mutex_locks'));

        $listener->addFactory('array', $factory);
        $listener->onKernelController($event);

        static::assertTrue($event->getRequest()->attributes->has('_ixarlie_mutex_locks'));

        $locks = $event->getRequest()->attributes->get('_ixarlie_mutex_locks');
        static::assertCount(1, $locks);

        /** @var LockInterface $lock */
        $lock = $locks[0];
        static::assertInstanceOf(LockInterface::class, $lock);

        static::assertTrue($lock->isAcquired());
    }

    /**
     * @param MutexRequest $annotation
     *
     * @return ControllerEvent
     */
    public function createEvent(MutexRequest $annotation): ControllerEvent
    {
        $request = Request::create('/homepage');
        $request->attributes->set('_ixarlie_mutex_request', [$annotation]);

        // _ixarlie_mutex_locks

        $http  = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event = new ControllerEvent(
            $http,
            function () {
            },
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        return $event;
    }
}
