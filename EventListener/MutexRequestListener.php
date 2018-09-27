<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Exception\MutexException;
use IXarlie\MutexBundle\Store\LockExecutor;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Lock\Factory;

/**
 * Class MutexRequestListener
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexRequestListener
{
    /**
     * @var Factory[]
     */
    private $factories;

    /**
     * @param string  $name
     * @param Factory $factory
     */
    public function addFactory($name, Factory $factory)
    {
        $this->factories[$name] = $factory;
    }

    /**
     * @param FilterControllerEvent $event
     *
     * @throws MutexException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (false === $event->isMasterRequest()) {
            return;
        }

        if (!is_array($controller = $event->getController())) {
            return;
        }

        $locks          = [];
        $request        = $event->getRequest();
        $configurations = $request->attributes->get('_ixarlie_mutex', []);

        /** @var MutexRequest $configuration */
        foreach ($configurations as $configuration) {
            $factory  = $this->getFactory($configuration);
            $executor = new LockExecutor($factory, $configuration);
            $lock     = $executor->execute();
            $locks[]  = $lock;
        }

        $request->attributes->set('_ixarlie_mutex_locks', $locks);
    }

    /**
     * @param MutexRequest $configuration
     *
     * @return Factory|null
     */
    private function getFactory(MutexRequest $configuration)
    {
        $id = $configuration->getService();
        if (!isset($this->factories[$id])) {
            throw new \RuntimeException(sprintf('Factory "%s" does not seem to exist.', $id));
        }

        return $this->factories[$id];
    }
}
