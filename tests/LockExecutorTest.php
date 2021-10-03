<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests;

use IXarlie\MutexBundle\Exception\MutexException;
use IXarlie\MutexBundle\LockExecutor;
use IXarlie\MutexBundle\LockingStrategy\LockingStrategy;
use IXarlie\MutexBundle\MutexRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * Class LockExecutorTest.
 */
final class LockExecutorTest extends TestCase
{
    public function testConfigurationWithoutName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Configuration must have a name.');

        $config = new MutexRequest([]);

        self::assertEmpty($config->name);

        $executor = new LockExecutor();
        $executor->execute($config);
    }

    public function testConfigurationServiceNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot find the "test" service.');

        $config = new MutexRequest(['name' => 'foo', 'service' => 'test']);

        $executor = new LockExecutor();
        $executor->execute($config);
    }

    public function testConfigurationStrategyNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot find the "test" strategy.');

        $config = new MutexRequest(['name' => 'foo', 'service' => 'lock.default.factory', 'strategy' => 'test']);

        $executor = new LockExecutor();
        $executor->addLockFactory('lock.default.factory', $this->createMock(LockFactory::class));

        $executor->execute($config);
    }

    public function testExecute(): void
    {
        $config   = new MutexRequest(['name' => 'foo', 'service' => 'lock.default.factory', 'strategy' => 'block']);
        $factory  = $this->createMock(LockFactory::class);
        $lock     = $this->createMock(LockInterface::class);
        $strategy = $this->createMock(LockingStrategy::class);

        $factory
            ->expects(self::once())
            ->method('createLock')
            ->with('foo', 300.0, true)
            ->willReturn($lock)
        ;

        $strategy
            ->expects(self::once())
            ->method('getName')
            ->willReturn('block')
        ;
        $strategy
            ->expects(self::once())
            ->method('execute')
            ->with($lock)
        ;

        $executor = new LockExecutor();
        $executor->addLockFactory('lock.default.factory', $factory);
        $executor->addLockStrategy($strategy);

        $result = $executor->execute($config);

        self::assertSame($lock, $result);
    }

    public function testExecuteThrowsException(): void
    {
        $this->expectException(MutexException::class);

        $config   = new MutexRequest(['name' => 'foo', 'service' => 'lock.default.factory', 'strategy' => 'block']);
        $factory  = $this->createMock(LockFactory::class);
        $lock     = $this->createMock(LockInterface::class);
        $strategy = $this->createMock(LockingStrategy::class);

        $factory
            ->expects(self::once())
            ->method('createLock')
            ->with('foo', 300.0, true)
            ->willReturn($lock)
        ;

        $strategy
            ->expects(self::once())
            ->method('getName')
            ->willReturn('block')
        ;
        $strategy
            ->expects(self::once())
            ->method('execute')
            ->with($lock)
            ->willThrowException(new LockAcquiringException())
        ;

        $executor = new LockExecutor();
        $executor->addLockFactory('lock.default.factory', $factory);
        $executor->addLockStrategy($strategy);

        $executor->execute($config);
    }
}
