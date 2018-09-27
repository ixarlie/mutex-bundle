<?php

namespace IXarlie\MutexBundle\Exception;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use Symfony\Component\Lock\LockInterface;

/**
 * Class MutexException.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexException extends \Exception
{
    /**
     * @var LockInterface
     */
    protected $lock;

    /**
     * @var MutexRequest
     */
    protected $configuration;

    /**
     * MutexException constructor.
     * @param LockInterface $lock
     * @param MutexRequest $configuration
     */
    public function __construct(LockInterface $lock, MutexRequest $configuration)
    {
        $this->lock = $lock;
        $this->configuration = $configuration;
    }

    /**
     * @return LockInterface
     */
    public function getLock(): LockInterface
    {
        return $this->lock;
    }

    /**
     * @return MutexRequest
     */
    public function getConfiguration(): MutexRequest
    {
        return $this->configuration;
    }
}
