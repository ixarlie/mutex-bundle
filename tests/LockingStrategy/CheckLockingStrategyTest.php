<?php declare(strict_types=1);

namespace Tests\IXarlie\MutexBundle\Tests\LockingStrategy;

use IXarlie\MutexBundle\LockingStrategy\CheckLockingStrategy;
use IXarlie\MutexBundle\LockingStrategy\LockingStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockInterface;

/**
 * Class CheckLockingStrategyTest.
 */
final class CheckLockingStrategyTest extends TestCase
{
    public function testInstance(): void
    {
        self::assertInstanceOf(LockingStrategy::class, new CheckLockingStrategy());
    }

    public function testGetName(): void
    {
        $strategy = new CheckLockingStrategy();

        self::assertSame('check', $strategy->getName());
    }

    public function testExecuteIsAcquired(): void
    {
        $this->expectException(LockAcquiringException::class);
        $this->expectExceptionMessage('Lock is already acquired.');

        $strategy = new CheckLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects(self::once())
            ->method('isAcquired')
            ->willReturn(true)
        ;

        $strategy->execute($lock);
    }

    public function testExecuteIsNotAcquired(): void
    {
        $strategy = new CheckLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects(self::once())
            ->method('isAcquired')
            ->willReturn(false)
        ;

        $lock
            ->expects(self::never())
            ->method('acquire')
        ;

        $strategy->execute($lock);
    }
}
