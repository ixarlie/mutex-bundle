<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\LockExecutor;
use IXarlie\MutexBundle\MutexRequest;
use IXarlie\MutexBundle\NamingStrategy\NamingStrategy;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ControllerListener.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class ControllerListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly LockExecutor   $executor,
        private readonly NamingStrategy $namingStrategy,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
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

        $attributes = $this->getAttributes($event)[MutexRequest::class] ?? null;
        if (empty($attributes)) {
            return;
        }

        $locks = [];

        foreach ($attributes as $attribute) {
            // Use a hash in order that any kind of locker can work properly.
            $name            = $this->namingStrategy->createName($attribute, $event->getRequest());
            $attribute->name = 'ixarlie_mutex_' . md5($name);
            $locks[]         = $this->executor->execute($attribute);
        }

        $event->getRequest()->attributes->set(MutexRequest::ATTRIBUTE, $locks);
    }

    /**
     * Backport for versions prior to 6.2
     *
     * @param ControllerEvent $event
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getAttributes(ControllerEvent $event): array
    {
        if (method_exists($event, 'getAttributes')) {
            return $event->getAttributes();
        }

        $controller = $event->getController();

        if (\is_array($controller) && method_exists(...$controller)) {
            $controllerReflector = new \ReflectionMethod(...$controller);
        } else if (\is_string($controller) && str_contains($controller, '::')) {
            $controllerReflector = new \ReflectionMethod($controller);
        } else {
            $controllerReflector = new \ReflectionFunction($controller(...));
        }

        if (\is_array($controller) && method_exists(...$controller)) {
            $class = new \ReflectionClass($controller[0]);
        } else if (\is_string($controller) && false !== $i = strpos($controller, '::')) {
            $class = new \ReflectionClass(substr($controller, 0, $i));
        } else {
            $class = str_contains(
                $controllerReflector->name,
                '{closure}'
            ) ? null : $controllerReflector->getClosureScopeClass();
        }
        $attributes = [];

        foreach (array_merge($class?->getAttributes() ?? [], $controllerReflector->getAttributes()) as $attribute) {
            if (class_exists($attribute->getName())) {
                $attributes[$attribute->getName()][] = $attribute->newInstance();
            }
        }

        return $attributes;
    }
}
