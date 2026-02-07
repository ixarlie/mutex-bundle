<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\LockingStrategy;

use IXarlie\MutexBundle\LockingStrategy\BlockLockingStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockInterface;

#[CoversClass(BlockLockingStrategy::class)]
final class BlockLockingStrategyTest extends TestCase
{
    public function testExecuteIsAcquired(): void
    {
        $this->expectException(LockAcquiringException::class);
        $this->expectExceptionMessage('Lock is already acquired.');

        $strategy = new BlockLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects($this->once())
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
            ->expects($this->once())
            ->method('acquire')
            ->with(false)
            ->willReturn(true)
        ;

        $strategy->execute($lock);
    }
}
