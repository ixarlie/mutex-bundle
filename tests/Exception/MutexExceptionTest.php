<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\Exception;

use IXarlie\MutexBundle\Exception\MutexException;
use IXarlie\MutexBundle\MutexRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MutexExceptionTest.
 */
final class MutexExceptionTest extends TestCase
{
    public function testInstance(): void
    {
        $config = new MutexRequest([]);

        self::assertInstanceOf(\Exception::class, new MutexException($config));
    }

    public function testStatusCode(): void
    {
        $config    = new MutexRequest([]);
        $exception = new MutexException($config);

        self::assertSame(Response::HTTP_LOCKED, $exception->getStatusCode());
    }

    public function testMessage(): void
    {
        $config    = new MutexRequest([]);
        $exception = new MutexException($config);

        self::assertSame('Resource is not available at this moment.', $exception->getMessage());
    }

    public function testCustomMessage(): void
    {
        $config    = new MutexRequest(['message' => 'foo']);
        $exception = new MutexException($config);

        self::assertSame('foo', $exception->getMessage());
    }

    public function testGetConfig(): void
    {
        $config    = new MutexRequest([]);
        $exception = new MutexException($config);

        self::assertSame($config, $exception->getConfig());
    }
}
