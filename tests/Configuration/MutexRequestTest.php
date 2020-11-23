<?php

namespace Tests\Configuration;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * Class MutexRequestTest
 */
final class MutexRequestTest extends TestCase
{
    public function testInstanceOf(): void
    {
        $annotation = new MutexRequest([]);
        static::assertInstanceOf(ConfigurationAnnotation::class, $annotation);
    }

    public function testProperties(): void
    {
        $params = [
            'name'          => 'my-mutex',
            'mode'          => MutexRequest::MODE_BLOCK,
            'ttl'           => 100,
            'service'       => 'default',
            'http'          => [
                'code'    => 418,
                'message' => 'Overwhelmed!',
                'domain'  => 'messages',
            ],
            'userIsolation' => true
        ];

        $annotation = new MutexRequest($params);
        $accessor   = new PropertyAccessor();

        foreach ($params as $paramName => $value) {
            static::assertEquals($value, $accessor->getValue($annotation, $paramName));
        }
    }

    public function testInvalidProperties(): void
    {
        $params = [
            'foo' => 'bar'
        ];

        $this->expectException(\RuntimeException::class);

        $annotation = new MutexRequest($params);
    }
}
