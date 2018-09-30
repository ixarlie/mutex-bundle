<?php

namespace Tests\EventListener;

use IXarlie\MutexBundle\EventListener\MutexReleaseListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Lock\LockInterface;

/**
 * Class MutexReleaseListenerTest.
 */
class MutexReleaseListenerTest extends TestCase
{
    public function testOnKernelTerminate()
    {
        $event    = $this->createEvent();
        $listener = new MutexReleaseListener();

        $listener->onKernelTerminate($event);

        static::assertFalse($event->getRequest()->attributes->has('_ixarlie_mutex_locks'));
    }

    /**
     * @return FinishRequestEvent
     */
    private function createEvent()
    {
        $lock    = $this->getMockBuilder(LockInterface::class)->getMock();
        $lock
            ->expects(static::once())
            ->method('release')
            ->willReturnSelf()
        ;

        $request = Request::create('/homepage');
        $request->attributes->set('_ixarlie_mutex_locks', [$lock]);

        $http    = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event   = new PostResponseEvent(
            $http,
            $request,
            Response::create()
        );

        return $event;
    }
}
