<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\LockingStrategy;

use IXarlie\MutexBundle\LockingStrategy\BlockLockingStrategy;
use IXarlie\MutexBundle\LockingStrategy\LockingStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockInterface;

/**
 * Class BlockLockingStrategyTest.
 */
final class BlockLockingStrategyTest extends TestCase
{
    public function testInstance(): void
    {
        self::assertInstanceOf(LockingStrategy::class, new BlockLockingStrategy());
    }

    public function testGetName(): void
    {
        $strategy = new BlockLockingStrategy();

        self::assertSame('block', $strategy->getName());
    }

    public function testExecuteIsAcquired(): void
    {
        $this->expectException(LockAcquiringException::class);
        $this->expectExceptionMessage('Lock is already acquired.');

        $strategy = new BlockLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects(self::once())
            ->method('acquire')
            ->willReturn(false)
        ;

        $strategy->execute($lock);
    }

    public function testExecuteIsNotAcquired(): void
    {
        $strategy = new BlockLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects(self::once())
            ->method('acquire')
            ->with(false)
            ->willReturn(true)
        ;

        $strategy->execute($lock);
    }
}
