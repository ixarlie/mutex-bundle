<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\LockingStrategy;

use Symfony\Component\Lock\LockInterface;

/**
 * Class ForceLockingStrategy.
 * It acquires the lock. Whether the lock is acquired, it forces a release before acquire it.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class ForceLockingStrategy implements LockingStrategy
{
    /**
     * @inheritDoc
     */
    public function execute(LockInterface $lock): void
    {
        if ($lock->isAcquired()) {
            $lock->release();
        }

        $lock->acquire(false);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'force';
    }
}
