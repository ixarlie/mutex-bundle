<?php

namespace Tests\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\EventListener\MutexExceptionListener;
use IXarlie\MutexBundle\Exception\MutexException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MutexExceptionListenerTest.
 */
class MutexExceptionListenerTest extends TestCase
{
    public function testException()
    {
        $annotation = new MutexRequest([
            'http' => [
                'code'    => Response::HTTP_BAD_REQUEST,
                'message' => 'My message'
            ]
        ]);

        $event    = $this->createEvent($annotation);
        $listener = new MutexExceptionListener();

        $listener->onKernelException($event);

        /** @var HttpException $exception */
        $exception   = $event->getException();
        $httpOptions = $annotation->getHttp();

        static::assertInstanceOf(HttpException::class, $exception);
        static::assertEquals($httpOptions['code'], $exception->getStatusCode());
        static::assertEquals($httpOptions['message'], $exception->getMessage());
        static::assertEquals(null, $httpOptions['domain']);
    }

    public function testTranslateWithoutDomain()
    {
        $message    = 'My message';
        $annotation = new MutexRequest([
            'http' => [
                'code'    => Response::HTTP_BAD_REQUEST,
                'message' => $message,
            ]
        ]);

        $event      = $this->createEvent($annotation);
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator
            ->expects(static::never())
            ->method('trans')
        ;

        $listener = new MutexExceptionListener($translator);

        $listener->onKernelException($event);

        /** @var HttpException $exception */
        $exception   = $event->getException();
        $httpOptions = $annotation->getHttp();

        static::assertInstanceOf(HttpException::class, $exception);
        static::assertEquals($httpOptions['code'], $exception->getStatusCode());
        static::assertEquals($httpOptions['message'], $exception->getMessage());
        static::assertEquals(null, $httpOptions['domain']);
    }

    public function testTranslateWithDomain()
    {
        $message    = 'foo.message';
        $annotation = new MutexRequest([
            'http' => [
                'code'    => Response::HTTP_BAD_REQUEST,
                'message' => $message,
                'domain'  => 'messages'
            ]
        ]);

        $event      = $this->createEvent($annotation);
        $translator = $this->getMockBuilder(TranslatorInterface::class)->getMock();
        $translator
            ->expects(static::once())
            ->method('trans')
            ->with($message, [], 'messages')
            ->willReturnCallback(function ($message) {
                return sprintf('[trans]%s[/trans]', $message);
            })
        ;

        $listener = new MutexExceptionListener($translator);

        $listener->onKernelException($event);

        /** @var HttpException $exception */
        $exception   = $event->getException();
        $httpOptions = $annotation->getHttp();

        static::assertInstanceOf(HttpException::class, $exception);
        static::assertEquals('[trans]foo.message[/trans]', $httpOptions['message']);
        static::assertEquals($httpOptions['code'], $exception->getStatusCode());
        static::assertEquals($httpOptions['message'], $exception->getMessage());
        static::assertEquals('messages', $httpOptions['domain']);
    }

    /**
     * @param MutexRequest $annotation
     *
     * @return GetResponseForExceptionEvent
     */
    private function createEvent(MutexRequest $annotation)
    {
        $request = Request::create('/homepage');
        $request->attributes->set('_ixarlie_mutex_request', [$annotation]);

        $http    = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $lock    = $this->getMockBuilder(LockInterface::class)->getMock();
        $event   = new GetResponseForExceptionEvent(
            $http,
            $request,
            HttpKernelInterface::MASTER_REQUEST,
            new MutexException($lock, $annotation)
        );

        return $event;
    }
}