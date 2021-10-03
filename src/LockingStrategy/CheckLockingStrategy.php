<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\LockingStrategy;

use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockInterface;

/**
 * Class CheckLockingStrategy.
 * Check the lock's status. Whether the lock is acquited then an exception is thrown.
 * This strategy does not attempt to acquire the lock.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class CheckLockingStrategy implements LockingStrategy
{
    /**
     * @inheritDoc
     */
    public function execute(LockInterface $lock): void
    {
        if (false === $lock->isAcquired()) {
            return;
        }

        throw new LockAcquiringException('Lock is already acquired.');
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'check';
    }
}
