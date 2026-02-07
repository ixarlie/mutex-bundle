<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\LockingStrategy;

use Symfony\Component\Lock\LockInterface;

/**
 * It acquires the lock. Whether the lock is acquired, it forces a release before acquire it.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
final class ForceLockingStrategy implements LockingStrategy
{
    public function execute(LockInterface $lock): void
    {
        if (false === $lock->acquire(false)) {
            $lock->release();
            $lock->acquire(false);
        }
    }
}
