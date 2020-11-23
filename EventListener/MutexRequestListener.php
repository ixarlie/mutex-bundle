<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Exception\MutexException;
use IXarlie\MutexBundle\Store\LockExecutor;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Lock\LockFactory;

/**
 * Class MutexRequestListener
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexRequestListener
{
    /**
     * @var LockFactory[]
     */
    private $factories;

    /**
     * @param string      $name
     * @param LockFactory $factory
     */
    public function addFactory(string $name, LockFactory $factory): void
    {
        $this->factories[$name] = $factory;
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws MutexException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if (false === $event->isMasterRequest()) {
            return;
        }

        $locks          = [];
        $request        = $event->getRequest();
        $configurations = $request->attributes->get('_ixarlie_mutex_request', []);

        /** @var MutexRequest $configuration */
        foreach ($configurations as $configuration) {
            $factory  = $this->getLockFactory($configuration);
            $executor = new LockExecutor($factory, $configuration);
            $lock     = $executor->execute();
            $locks[]  = $lock;
        }

        $request->attributes->set('_ixarlie_mutex_locks', $locks);
    }

    /**
     * @param MutexRequest $configuration
     *
     * @return LockFactory
     */
    private function getLockFactory(MutexRequest $configuration): LockFactory
    {
        $id = $configuration->getService();
        if (!isset($this->factories[$id])) {
            throw new \RuntimeException(sprintf('Factory "%s" does not seem to exist.', $id));
        }

        return $this->factories[$id];
    }
}
