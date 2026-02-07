<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\EventListener;

use IXarlie\MutexBundle\EventListener\ControllerListener;
use IXarlie\MutexBundle\LockExecutor;
use IXarlie\MutexBundle\MutexRequest;
use IXarlie\MutexBundle\NamingStrategy\NamingStrategy;
use IXarlie\MutexBundle\Tests\Fixtures\DemoController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Lock\LockInterface;

#[CoversClass(ControllerListener::class)]
final class ControllerListenerTest extends TestCase
{
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
        $kernel     = self::createStub(HttpKernelInterface::class);
        $event      = new ControllerEvent($kernel, $controller, $request, HttpKernelInterface::SUB_REQUEST);

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

        $listener = new ControllerListener($executor, $naming);
        $listener->onKernelController($event);

        self::assertEmpty($request->attributes->get('_ixarlie_mutex_locks'));
    }

    public function testMainRequest(): void
    {
        $request    = Request::create('');
        $controller = [new DemoController(), 'block'];
        $kernel     = self::createStub(HttpKernelInterface::class);
        $event      = new ControllerEvent($kernel, $controller, $request, HttpKernelInterface::MAIN_REQUEST);

        $lock     = self::createStub(LockInterface::class);
        $executor = $this->createMock(LockExecutor::class);
        $executor
            ->expects(self::once())
            ->method('execute')
            ->with(self::isInstanceOf(MutexRequest::class))
            ->willReturn($lock)
        ;

        $naming = $this->createMock(NamingStrategy::class);
        $naming
            ->expects(self::once())
            ->method('createName')
            ->with(self::isInstanceOf(MutexRequest::class), $request)
            ->willReturn('lock_name')
        ;

        $listener = new ControllerListener($executor, $naming);
        $listener->onKernelController($event);

        self::assertCount(1, $request->attributes->get('_ixarlie_mutex_locks'));
    }

    public function testNotSeveralAnnotations(): void
    {
        $request    = Request::create('');
        $controller = [new DemoController(), 'double'];
        $kernel     = self::createStub(HttpKernelInterface::class);
        $event      = new ControllerEvent($kernel, $controller, $request, HttpKernelInterface::MAIN_REQUEST);

        $lock     = self::createStub(LockInterface::class);
        $executor = $this->createMock(LockExecutor::class);
        $executor
            ->expects(self::exactly(2))
            ->method('execute')
            ->with(self::isInstanceOf(MutexRequest::class))
            ->willReturn($lock)
        ;

        $naming = $this->createMock(NamingStrategy::class);
        $naming
            ->expects(self::exactly(2))
            ->method('createName')
            ->with(
                self::callback(static function($arg) {
                    static $i = 0;

                    return match (++$i) {
                        1, 2    => $arg instanceof MutexRequest,
                        default => false,
                    };
                }),
                self::callback(static function($arg) use ($request) {
                    static $i = 0;

                    return match (++$i) {
                        1, 2    => $arg === $request,
                        default => false,
                    };
                })
            )
            ->willReturnOnConsecutiveCalls(
                'lock_name_1',
                'lock_name_2'
            )
        ;

        $listener = new ControllerListener($executor, $naming);
        $listener->onKernelController($event);

        self::assertCount(2, $request->attributes->get('_ixarlie_mutex_locks'));
    }
}
