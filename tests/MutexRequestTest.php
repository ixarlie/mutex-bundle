<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests;

use IXarlie\MutexBundle\MutexRequest;
use PHPUnit\Framework\TestCase;

/**
 * Class MutexRequestTest
 */
final class MutexRequestTest extends TestCase
{
    public function testProperties(): void
    {
        $params = [
            'name'          => 'my-mutex',
            'strategy'      => 'block',
            'ttl'           => 100,
            'service'       => 'default',
            'message'       => 'Unavailable',
            'userIsolation' => true,
        ];

        $annotation = new MutexRequest($params);

        self::assertSame('my-mutex', $annotation->name);
        self::assertSame('block', $annotation->strategy);
        self::assertSame(100, $annotation->ttl);
        self::assertSame('default', $annotation->service);
        self::assertSame('Unavailable', $annotation->message);
        self::assertTrue($annotation->userIsolation);
    }

    public function testInvalidProperties(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $params = [
            'foo' => 'bar',
        ];

        new MutexRequest($params);
    }
}
