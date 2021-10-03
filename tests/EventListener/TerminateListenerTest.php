<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\EventListener;

use IXarlie\MutexBundle\EventListener\TerminateListener;
use IXarlie\MutexBundle\Tests\Fixtures\TestKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Lock\LockInterface;

/**
 * Class TerminateListenerTest.
 */
final class TerminateListenerTest extends TestCase
{
    public function testInstance(): void
    {
        $listener = new TerminateListener();

        self::assertInstanceOf(EventSubscriberInterface::class, $listener);
    }

    public function testEvents(): void
    {
        self::assertSame(
            [
                KernelEvents::TERMINATE => 'onKernelTerminate',
            ],
            TerminateListener::getSubscribedEvents()
        );
    }

    public function testReleaseLocks(): void
    {
        $listener = new TerminateListener();

        $request  = Request::create('');
        $response = new Response();
        $kernel   = $this->createMock(HttpKernelInterface::class);
        $event    = new TerminateEvent($kernel, $request, $response);

        $lock1 = $this->createMock(LockInterface::class);
        $lock2 = $this->createMock(LockInterface::class);

        $lock1
            ->expects(self::once())
            ->method('release')
        ;
        $lock2
            ->expects(self::once())
            ->method('release')
        ;

        $request->attributes->set('_ixarlie_mutex_locks', [$lock1, $lock2]);

        $listener->onKernelTerminate($event);

        self::assertNull($request->attributes->get('_ixarlie_mutex_locks'));
    }
}
