<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\LockExecutor;
use IXarlie\MutexBundle\MutexRequest;
use IXarlie\MutexBundle\NamingStrategy\NamingStrategy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
final class ControllerListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LockExecutor   $executor,
        private readonly NamingStrategy $namingStrategy,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    /**
     * @throws \ReflectionException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if (false === $event->isMainRequest()) {
            return;
        }

        $attributes = $event->getAttributes()[MutexRequest::class] ?? null;
        if (!is_array($attributes) || 0 === count($attributes)) {
            return;
        }

        $locks = [];

        /** @var MutexRequest $attribute */
        foreach ($attributes as $attribute) {
            // Use a hash in order that any kind of locker can work properly.
            $name            = $this->namingStrategy->createName($attribute, $event->getRequest());
            $attribute->name = 'ixarlie_mutex_' . md5($name);
            $locks[]         = $this->executor->execute($attribute);
        }

        $event->getRequest()->attributes->set(MutexRequest::ATTRIBUTE, $locks);
    }
}
