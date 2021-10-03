<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
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
    /**
     * @var LockExecutor
     */
    private $executor;

    /**
     * @var NamingStrategy
     */
    private $namingStrategy;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param LockExecutor   $executor
     * @param NamingStrategy $namingStrategy
     * @param Reader         $reader
     */
    public function __construct(LockExecutor $executor, NamingStrategy $namingStrategy, Reader $reader)
    {
        $this->executor       = $executor;
        $this->namingStrategy = $namingStrategy;
        $this->reader         = $reader;
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
     * @param ControllerEvent $event
     *
     * @throws \ReflectionException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if (false === $this->isMainRequest($event)) {
            return;
        }

        $controller = $event->getController();

        if (!is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!is_array($controller)) {
            return;
        }

        $className      = self::getRealClass(\get_class($controller[0]));
        $object         = new \ReflectionClass($className);
        $method         = $object->getMethod($controller[1]);
        $configurations = $this->getConfigurations($this->reader->getMethodAnnotations($method));

        $locks = [];

        foreach ($configurations as $configuration) {
            // Use a hash in order that any kind of locker can work properly.
            $name                = $this->namingStrategy->createName($configuration, $event->getRequest());
            $configuration->name = 'ixarlie_mutex_' . md5($name);
            $locks[]             = $this->executor->execute($configuration);
        }

        $event->getRequest()->attributes->set('_ixarlie_mutex_locks', $locks);
    }

    /**
     * @param array $annotations
     *
     * @return MutexRequest[]
     */
    private function getConfigurations(array $annotations): array
    {
        $result = [];

        foreach ($annotations as $configuration) {
            if ($configuration instanceof MutexRequest) {
                $result[] = $configuration;
            }
        }

        return $result;
    }

    /**
     * @param ControllerEvent $event
     *
     * @return bool
     */
    private function isMainRequest(ControllerEvent $event): bool
    {
        return method_exists($event, 'isMainRequest') ? $event->isMainRequest() : $event->isMasterRequest();
    }

    /**
     * @param string $class
     *
     * @return string
     */
    private static function getRealClass(string $class): string
    {
        if (class_exists(Proxy::class)) {
            if (false === $pos = strrpos($class, '\\' . Proxy::MARKER . '\\')) {
                return $class;
            }

            return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
        }

        return $class;
    }
}
