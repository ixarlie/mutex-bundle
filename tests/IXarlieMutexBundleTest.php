<?php

namespace IXarlie\MutexBundle\Tests;

use IXarlie\MutexBundle\IXarlieMutexBundle;
use IXarlie\MutexBundle\Tests\Fixtures\TokenStorage;
use IXarlie\MutexBundle\Tests\Fixtures\Translator;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class IXarlieMutexBundleTest
 */
class IXarlieMutexBundleTest extends TestCase
{
    use UtilTestTrait;

    public function testInstance()
    {
        $bundle = new IXarlieMutexBundle();
        static::assertInstanceOf(Bundle::class, $bundle);
    }

    public function testBootBundle()
    {
        $container = $this->getContainer();
        $bundle    = new IXarlieMutexBundle();
        $config    = [
            'default' => 'flock.default',
            'request_listener' => [
                'queue_timeout' => 60,
                'queue_max_try' => 8,
                'translator' => true,
                'user_isolation' => true,
                'http_exception' => [
                    'message' => 'No way!',
                    'code' => 418,
                ],
                'priority' => 1000
            ],
            'flock' => ['default' => ['cache_dir' => '/tmp']],
            'redis' => ['default' => ['host' => 'localhost', 'port' => 6379]],
            'predis' => ['default' => ['connection' => ['host' => 'localhost', 'port' => 6379]]],
            'memcached' => ['default' => ['host' => 'localhost', 'port' => 6379]],
        ];
        $this->prepareContainer($container, $bundle, $config);

        // Container does not have translator nor user_isolation
        $definition = $container->getDefinition('i_xarlie_mutex.controller.listener');

        // calls methods
        static::assertFalse($definition->hasMethodCall('setTranslator'));
        static::assertFalse($definition->hasMethodCall('setTokenStorage'));
        $map = [
            'setMaxQueueTimeout'      => 'queue_timeout',
            'setMaxQueueTry'          => 'queue_max_try',
            'setHttpExceptionOptions' => 'http_exception',
        ];
        foreach ($definition->getMethodCalls() as list($method, $params)) {
            if (isset($map[$method])) {
                $configValue = $config['request_listener'][$map[$method]];
                $configValue = is_array($configValue) ? array_values($configValue) : [$configValue];
                static::assertEquals($configValue, $params);
            } elseif ($method === 'addLockerManager') {
                if ($params[0] !== 'i_xarlie_mutex.locker') {
                    $params = explode('.', $params[0]);
                } else {
                    $params = explode('.', $params[1]);
                }
                $params[1] = str_replace('locker_', '', $params[1]);
                static::assertArrayHasKey($params[1], $config);
                static::assertArrayHasKey($params[2], $config[$params[1]]);
            }
        }

        // priority tag
        static::assertNotNull($tag = $definition->getTag('kernel.event_listener'));
        static::assertCount(2, $tag);
        $tags = [
            [
                'event'    => KernelEvents::CONTROLLER,
                'method'   => 'onKernelController',
                'priority' => $config['request_listener']['priority'],
            ],
            [
                'event'  => KernelEvents::TERMINATE,
                'method' => 'onKernelTerminate',
            ]
        ];
        foreach ($tags as $i => $params) {
            foreach ($params as $key => $value) {
                static::assertArrayHasKey($key, $tag[$i]);
                static::assertEquals($value, $tag[$i][$key]);
            }
        }
    }

    public function testCompileWithTranslator()
    {
        $container = $this->getContainer();
        // Add a double translator
        $container->setDefinition('translator', new Definition(Translator::class));
        $bundle = new IXarlieMutexBundle();

        $this->prepareContainer($container, $bundle, [
            'default' => 'flock.default',
            'request_listener' => [
                'translator' => true
            ],
            'flock' => [
                'default' => [
                    'cache_dir' => '/tmp'
                ]
            ]
        ]);

        $definition = $container->getDefinition('ixarlie_mutex.controller.listener');
        static::assertTrue($definition->hasMethodCall('setTranslator'));
        static::assertFalse($definition->hasMethodCall('setTokenStorage'));
    }

    public function testCompileWithSecurityToken()
    {
        $container = $this->getContainer();
        // Add a double token storage
        $container->setDefinition('security.token_storage', new Definition(TokenStorage::class));
        $bundle = new IXarlieMutexBundle();

        $this->prepareContainer($container, $bundle, [
            'default' => 'flock.default',
            'request_listener' => [
                'user_isolation' => true
            ],
            'flock' => [
                'default' => [
                    'cache_dir' => '/tmp'
                ]
            ]
        ]);

        $definition = $container->getDefinition('ixarlie_mutex.controller.listener');
        static::assertTrue($definition->hasMethodCall('setTokenStorage'));
        static::assertFalse($definition->hasMethodCall('setTranslator'));
    }
}
