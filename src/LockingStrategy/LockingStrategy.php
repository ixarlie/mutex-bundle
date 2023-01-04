<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\LockingStrategy;

use Symfony\Component\Lock\Exception\ExceptionInterface;
use Symfony\Component\Lock\LockInterface;

/**
 * Interface LockingStrategy
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
interface LockingStrategy
{
    /**
     * @throws ExceptionInterface
     */
    public function execute(LockInterface $lock): void;

    /**
     * Returns the name for the strategy.
     */
    public function getName(): string;
}
