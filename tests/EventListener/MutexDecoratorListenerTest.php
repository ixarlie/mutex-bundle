<?php

namespace Tests\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\EventListener\MutexDecoratorListener;
use IXarlie\MutexBundle\Exception\MutexConfigurationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class MutexDecoratorListenerTest.
 */
final class MutexDecoratorListenerTest extends TestCase
{
    /**
     * @dataProvider dataNameProvider
     *
     * @param string|null $name
     * @param bool        $isolated
     * @param array|null  $placeholders
     */
    public function testDecorateName(string $name = null, bool $isolated = false, array $placeholders = null): void
    {
        $annotation = new MutexRequest([
            'name'          => $name,
            'userIsolation' => $isolated,
        ]);
        $event      = $this->createEvent($annotation);

        if (null === $name) {
            static::assertEmpty($annotation->getName());
        } else {
            static::assertNotNull($annotation->getName());
        }

        if ($isolated) {
            // Mock token and assert the token is used for the name creation.
            $storage = new TokenStorage();
            $token   = $this->getMockBuilder(TokenInterface::class)->getMock();
            $token
                ->expects(static::once())
                ->method('serialize')
                ->willReturn('1234567890ABCDEF')
            ;
            $storage->setToken($token);
        } else {
            $storage = null;
        }

        $listener = new MutexDecoratorListener($storage);


        if (is_array($placeholders)) {
            $event->getRequest()->attributes->set('_route_params', $placeholders);
        }

        $listener->onKernelController($event);

        static::assertNotEmpty($annotation->getName());
        static::assertStringStartsWith('ixarlie_mutex_', $annotation->getName());
    }

    public function dataNameProvider(): \Generator
    {
        yield [
            'mutex_name', // name
            false,        // user isolation,
            null          // request placeholders
        ];
        yield [
            'mutex_name',
            true,
            null,
        ];
        yield [
            'mutex_name_{id1}_{id2}',
            false,
            ['id1' => 25, 'id2' => 10],
        ];
        yield [
            'mutex_name_{id1}_{id2}',
            true,
            ['id1' => 25, 'id2' => 10],
        ];
        yield [
            null,
            false,
            null,
        ];
        yield [
            null,
            true,
            null,
        ];
    }

    public function testDecorateNameIsolatedMisconfiguration(): void
    {
        $annotation = new MutexRequest([
            'userIsolation' => true,
        ]);
        $event      = $this->createEvent($annotation);

        static::assertEmpty($annotation->getName());

        $listener = new MutexDecoratorListener();

        $this->expectException(MutexConfigurationException::class);
        $this->expectExceptionMessage('[MutexDecoratorListener] Cannot use isolation with missing "security.token_storage".');

        $listener->onKernelController($event);
    }

    public function testDecorateNameMissingPlaceholder(): void
    {
        $annotation = new MutexRequest(['name' => 'test_{id}']);
        $event      = $this->createEvent($annotation);
        $listener   = new MutexDecoratorListener();

        static::assertNotEmpty($annotation->getName());

        $this->expectException(MutexConfigurationException::class);
        $this->expectExceptionMessage('[MutexDecoratorListener] Cannot find placeholder "id" in request for name "test_{id}"');

        $listener->onKernelController($event);
    }

    /**
     * @dataProvider dataServiceProvider
     *
     * @param string|null $service
     * @param string      $expectedService
     */
    public function testDecorateService(?string $service, string $expectedService): void
    {
        $annotation = new MutexRequest(['service' => $service]);
        $event      = $this->createEvent($annotation);
        $listener   = new MutexDecoratorListener();

        if (null === $service) {
            static::assertEmpty($annotation->getService());
        } else {
            static::assertNotNull($annotation->getService());
        }

        $listener->onKernelController($event);

        static::assertEquals($expectedService, $annotation->getService());
    }

    /**
     * @return \Generator
     */
    public function dataServiceProvider(): \Generator
    {
        yield [
            null,
            'ixarlie_mutex.default_factory',
        ];
        yield [
            'ixarlie_mutex.flock_factory.default',
            'ixarlie_mutex.flock_factory.default',
        ];
        yield [
            'flock.default',
            'ixarlie_mutex.flock_factory.default',
        ];
    }

    /**
     * @param MutexRequest $annotation
     *
     * @return ControllerEvent
     */
    private function createEvent(MutexRequest $annotation): ControllerEvent
    {
        $request = Request::create('/homepage');
        $request->attributes->set('_ixarlie_mutex_request', [$annotation]);
        $request->attributes->set('_controller', 'App\ControllerName:methodName');

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
