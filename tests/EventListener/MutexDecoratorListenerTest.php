<?php

namespace Tests\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\EventListener\MutexDecoratorListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class MutexDecoratorListenerTest.
 */
class MutexDecoratorListenerTest extends TestCase
{
    /**
     * @dataProvider dataNameProvider
     * @param string $name
     * @param bool   $isolated
     * @param array  $placeholders
     */
    public function testDecorateName($name = null, $isolated = false, array $placeholders = null)
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

    public function dataNameProvider()
    {
        yield [
            'mutex_name', // name
            false,        // user isolation,
            null          // request placeholders
        ];
        yield [
            'mutex_name',
            true,
            null
        ];
        yield [
            'mutex_name_{id1}_{id2}',
            false,
            ['id1' => 25, 'id2' => 10]
        ];
        yield [
            'mutex_name_{id1}_{id2}',
            true,
            ['id1' => 25, 'id2' => 10]
        ];
        yield [
            null,
            false,
            null
        ];
        yield [
            null,
            true,
            null
        ];
    }

    /**
     * @expectedException \IXarlie\MutexBundle\Exception\MutexConfigurationException
     * @expectedExceptionMessage [MutexDecoratorListener] Cannot use isolation with missing "security.token_storage".
     */
    public function testDecorateNameIsolatedMisconfiguration()
    {
        $annotation = new MutexRequest([
            'userIsolation' => true,
        ]);
        $event      = $this->createEvent($annotation);

        static::assertEmpty($annotation->getName());

        $listener = new MutexDecoratorListener();

        $listener->onKernelController($event);
    }

    /**
     * @expectedException \IXarlie\MutexBundle\Exception\MutexConfigurationException
     * @expectedExceptionMessage [MutexDecoratorListener] Cannot find placeholder "id" in request for name "test_{id}"
     */
    public function testDecorateNameMissingPlaceholder()
    {
        $annotation = new MutexRequest(['name' => 'test_{id}']);
        $event      = $this->createEvent($annotation);
        $listener   = new MutexDecoratorListener();

        static::assertNotEmpty($annotation->getName());

        $listener->onKernelController($event);
    }

    /**
     * @dataProvider dataServiceProvider
     * @param string $service
     * @param string $expectedService
     */
    public function testDecorateService($service = null, $expectedService)
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
     * @return array
     */
    public function dataServiceProvider()
    {
        yield [
            null,
            'ixarlie_mutex.default_factory'
        ];
        yield [
            'ixarlie_mutex.flock_factory.default',
            'ixarlie_mutex.flock_factory.default'
        ];
        yield [
            'flock.default',
            'ixarlie_mutex.flock_factory.default'
        ];
    }

    /**
     * @param MutexRequest $annotation
     *
     * @return FilterControllerEvent
     */
    private function createEvent(MutexRequest $annotation)
    {
        $request = Request::create('/homepage');
        $request->attributes->set('_ixarlie_mutex_request', [$annotation]);
        $request->attributes->set('_controller', 'App\ControllerName:methodName');

        $http    = $this->getMockBuilder(HttpKernelInterface::class)->getMock();
        $event   = new FilterControllerEvent(
            $http,
            function () {
            },
            $request,
            HttpKernelInterface::MASTER_REQUEST
        );

        return $event;
    }
}
