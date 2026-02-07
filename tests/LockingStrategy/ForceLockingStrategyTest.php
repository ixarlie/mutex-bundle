<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\LockingStrategy;

use IXarlie\MutexBundle\LockingStrategy\ForceLockingStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockInterface;

#[CoversClass(ForceLockingStrategy::class)]
final class ForceLockingStrategyTest extends TestCase
{
    public function testExecuteIsAcquired(): void
    {
        $strategy = new ForceLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects($this->once())
            ->method('release')
        ;
        $lock
            ->expects($this->exactly(2))
            ->method('acquire')
            ->with(
                self::callback(static function($arg) {
                    static $i = 0;

                    return match (++$i) {
                        1, 2    => false === $arg,
                        default => false,
                    };
                })
            )
            ->willReturnOnConsecutiveCalls(false, true)
        ;

        $strategy->execute($lock);
    }

    public function testExecuteIsNotAcquired(): void
    {
        $strategy = new ForceLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects($this->never())
            ->method('release')
        ;
        $lock
            ->expects($this->once())
            ->method('acquire')
            ->with(false)
            ->willReturn(true)
        ;

        $strategy->execute($lock);
    }
}
