<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Model\LockerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class MutexListener
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(KernelEvents::CONTROLLER => 'onKernelController');
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request = $event->getRequest();
        /** @var MutexRequest $configuration */
        if (!$configuration = $request->attributes->get('_mutex')) {
            return;
        }

        $service = $this->getMutexService($configuration->getService());
        if (null === $service) {
            throw new \LogicException(sprintf(
                'To use the @Mutex tag, you need to register a valid locker provider. %s is not registered',
                $configuration->getService()
            ));
        }

        switch ($configuration->getMode()) {
            case MutexRequest::MODE_BLOCK:
                $this->handleBlock($service, $configuration);
                break;
            case MutexRequest::MODE_POLL:
                $this->handlePoll($service, $configuration);
                break;
            default:
        }

    }

    /**
     * @param string $serviceId
     * @return LockerManagerInterface|null
     */
    private function getMutexService($serviceId)
    {
        try {
            return $this->container->get($serviceId);
        } catch (ServiceNotFoundException $e) {
        }
        return;
    }

    /**
     * @param LockerManagerInterface $service
     * @param MutexRequest           $configuration
     */
    private function handleBlock(LockerManagerInterface $service, MutexRequest $configuration)
    {
        $this->handlePoll($service, $configuration);
        $service->acquireLock($configuration->getName(), null, $configuration->getTtl());
    }

    /**
     * @param LockerManagerInterface $service
     * @param MutexRequest           $configuration
     */
    private function handlePoll(LockerManagerInterface $service, MutexRequest $configuration)
    {
        if ($service->isLocked($configuration->getName())) {
            $message = $configuration->getMessage();
            if (!$message) {
                $message = 'Resource is not available at this moment';
            }
            throw new ConflictHttpException($message);
        }
    }
}
