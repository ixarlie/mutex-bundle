<?php

namespace IXarlie\MutexBundle\Tests;

use IXarlie\MutexBundle\IXarlieMutexBundle;
use IXarlie\MutexBundle\Tests\Fixtures\TokenStorage;
use IXarlie\MutexBundle\Tests\Fixtures\Translator;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class IXarlieMutexBundleTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexBundleTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;

    public function testInstance()
    {
        $bundle = new IXarlieMutexBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
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
            'memcache' => ['default' => ['host' => 'localhost', 'port' => 6379]],
            'memcached' => ['default' => ['host' => 'localhost', 'port' => 6379]],
        ];
        $this->prepareContainer($container, $bundle, $config);
        
        // Container does not have translator nor user_isolation
        $definition = $container->getDefinition('i_xarlie_mutex.controller.listener');
        
        // calls methods
        $this->assertFalse($definition->hasMethodCall('setTranslator'));
        $this->assertFalse($definition->hasMethodCall('setTokenStorage'));
        $map = [
            'setMaxQueueTimeout'      => 'queue_timeout',
            'setMaxQueueTry'          => 'queue_max_try',
            'setHttpExceptionOptions' => 'http_exception',
        ];
        foreach ($definition->getMethodCalls() as list($method, $params)) {
            if (isset($map[$method])) {
                $configValue = $config['request_listener'][$map[$method]];
                $configValue = is_array($configValue) ? array_values($configValue) : [$configValue];
                $this->assertEquals($configValue, $params);
            } elseif ($method === 'addLockerManager') {
                if ($params[0] !== 'i_xarlie_mutex.locker') {
                    $params = explode('.', $params[0]);
                } else {
                    $params = explode('.', $params[1]);
                }
                $params[1] = str_replace('locker_', '', $params[1]);
                $this->assertArrayHasKey($params[1], $config);
                $this->assertArrayHasKey($params[2], $config[$params[1]]);
            }
        }
        
        // priority tag
        $this->assertNotNull($tag = $definition->getTag('kernel.event_subscriber'));
        $this->assertCount(1, $tag);
        $this->assertArrayHasKey('priority', $tag[0]);
        $this->assertEquals($tag[0]['priority'], $config['request_listener']['priority']);
    }
    
    public function testCompileWithTranslator()
    {
        $container = $this->getContainer();
        // Add a double translator
        $container->set('translator', new Definition(Translator::class));
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

        $definition = $container->getDefinition('i_xarlie_mutex.controller.listener');
        $this->assertTrue($definition->hasMethodCall('setTranslator'));
        $this->assertFalse($definition->hasMethodCall('setTokenStorage'));
    }
    
    public function testCompileWithSecurityToken()
    {
        $container = $this->getContainer();
        // Add a double token storage
        $container->set('security.token_storage', new Definition(TokenStorage::class));
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

        $definition = $container->getDefinition('i_xarlie_mutex.controller.listener');
        $this->assertTrue($definition->hasMethodCall('setTokenStorage'));
        $this->assertFalse($definition->hasMethodCall('setTranslator'));
    }
}
