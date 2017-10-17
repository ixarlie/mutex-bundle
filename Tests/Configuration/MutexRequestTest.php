<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\Configuration\MutexRequest;

/**
 * Class MutexRequestTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testInstanceOf()
    {
        $annotation = new MutexRequest([]);
        $this->assertInstanceOf(MutexRequest::class, $annotation);
    }
    
    public function testProperties()
    {
        $params = [
            'name'          => 'my-mutex',
            'mode'          => MutexRequest::MODE_BLOCK,
            'ttl'           => 100,
            'service'       => 'default',
            'httpCode'      => 418,
            'message'       => 'Overwhelmed!',
            'messageDomain' => 'messages',
            'userIsolation' => true
        ];
        
        $annotation = new MutexRequest($params);
        
        $refClass = new \ReflectionClass(MutexRequest::class);
        foreach ($refClass->getProperties() as $property) {
            $property->setAccessible(true);

            $name  = $property->getName();
            $this->assertArrayHasKey($name, $params);

            $value = $property->getValue($annotation);
            $this->assertEquals($params[$name], $value);
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidProperties()
    {
        $params = [
            'foo' => 'bar'
        ];

        $annotation = new MutexRequest($params);
    }
}
