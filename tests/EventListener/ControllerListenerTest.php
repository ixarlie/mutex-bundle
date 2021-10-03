<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\EventListener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\Reader;
use IXarlie\MutexBundle\EventListener\ControllerListener;
use IXarlie\MutexBundle\LockExecutor;
use IXarlie\MutexBundle\MutexRequest;
use IXarlie\MutexBundle\NamingStrategy\NamingStrategy;
use IXarlie\MutexBundle\Tests\Fixtures\DemoController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Lock\LockInterface;

/**
 * Class ControllerListenerTest.
 */
final class ControllerListenerTest extends TestCase
{
    public function testInstance(): void
    {
        $listener = new ControllerListener(
            $this->createMock(LockExecutor::class),
            $this->createMock(NamingStrategy::class),
            $this->createMock(Reader::class)
        );

        self::assertInstanceOf(EventSubscriberInterface::class, $listener);
    }

    public function testEvents(): void
    {
        self::assertSame(
            [
                KernelEvents::CONTROLLER => 'onKernelController',
            ],
            ControllerListener::getSubscribedEvents()
        );
    }

    public function testNotMainRequest(): void
    {
        $request    = Request::create('');
        $controller = [new DemoController(), 'block'];
        $kernel     = $this->createMock(HttpKernelInterface::class);
        $event      = new ControllerEvent($kernel, $controller, $request, 2);

        $executor = $this->createMock(LockExecutor::class);
        $executor
            ->expects(self::never())
            ->method('execute')
        ;

        $naming = $this->createMock(NamingStrategy::class);
        $naming
            ->expects(self::never())
            ->method('createName')
        ;

        $listener = new ControllerListener($executor, $naming, new AnnotationReader());
        $listener->onKernelController($event);


        self::assertEmpty($request->attributes->get('_ixarlie_mutex_locks'));
    }

    public function testMainRequest(): void
    {
        $request    = Request::create('');
        $controller = [new DemoController(), 'block'];
        $kernel     = $this->createMock(HttpKernelInterface::class);
        $event      = new ControllerEvent($kernel, $controller, $request, 1);

        $lock     = $this->createMock(LockInterface::class);
        $executor = $this->createMock(LockExecutor::class);
        $executor
            ->expects(self::once())
            ->method('execute')
            ->with($this->isInstanceOf(MutexRequest::class))
            ->willReturn($lock)
        ;

        $naming = $this->createMock(NamingStrategy::class);
        $naming
            ->expects(self::once())
            ->method('createName')
            ->with($this->isInstanceOf(MutexRequest::class), $request)
            ->willReturn('lock_name')
        ;

        $listener = new ControllerListener($executor, $naming, new AnnotationReader());
        $listener->onKernelController($event);


        self::assertCount(1, $request->attributes->get('_ixarlie_mutex_locks'));
    }

    public function testNotSeveralAnnotations(): void
    {
        $request    = Request::create('');
        $controller = [new DemoController(), 'double'];
        $kernel     = $this->createMock(HttpKernelInterface::class);
        $event      = new ControllerEvent($kernel, $controller, $request, 1);

        $lock     = $this->createMock(LockInterface::class);
        $executor = $this->createMock(LockExecutor::class);
        $executor
            ->expects(self::exactly(2))
            ->method('execute')
            ->with($this->isInstanceOf(MutexRequest::class))
            ->willReturn($lock)
        ;

        $naming = $this->createMock(NamingStrategy::class);
        $naming
            ->expects(self::exactly(2))
            ->method('createName')
            ->withConsecutive(
                [$this->isInstanceOf(MutexRequest::class), $request],
                [$this->isInstanceOf(MutexRequest::class), $request]
            )
            ->willReturnOnConsecutiveCalls(
                'lock_name_1',
                'lock_name_2'
            )
        ;

        $listener = new ControllerListener($executor, $naming, new AnnotationReader());
        $listener->onKernelController($event);


        self::assertCount(2, $request->attributes->get('_ixarlie_mutex_locks'));
    }
}
