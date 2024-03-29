<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\LockingStrategy;

use IXarlie\MutexBundle\LockingStrategy\ForceLockingStrategy;
use IXarlie\MutexBundle\LockingStrategy\LockingStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockInterface;

/**
 * Class ForceLockingStrategyTest.
 */
final class ForceLockingStrategyTest extends TestCase
{
    public function testInstance(): void
    {
        self::assertInstanceOf(LockingStrategy::class, new ForceLockingStrategy());
    }

    public function testGetName(): void
    {
        $strategy = new ForceLockingStrategy();

        self::assertSame('force', $strategy->getName());
    }

    public function testExecuteIsAcquired(): void
    {
        $strategy = new ForceLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects(self::once())
            ->method('release')
        ;
        $lock
            ->expects(self::exactly(2))
            ->method('acquire')
            ->withConsecutive([false], [false])
            ->willReturnOnConsecutiveCalls(false, true)
        ;

        $strategy->execute($lock);
    }

    public function testExecuteIsNotAcquired(): void
    {
        $strategy = new ForceLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects(self::never())
            ->method('release')
        ;
        $lock
            ->expects(self::once())
            ->method('acquire')
            ->with(false)
            ->willReturn(true)
        ;

        $strategy->execute($lock);
    }
}
