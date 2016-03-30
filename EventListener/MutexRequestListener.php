<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Model\LockerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Util\ClassUtils;

/**
 * Class MutexRequestListener
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexRequestListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->reader    = $container->get('annotation_reader');
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => 'onKernelController',
            KernelEvents::TERMINATE  => 'onKernelTerminate'
        );
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $configurations = $this->loadConfiguration($event);
        if (empty($configurations)) {
            return;
        }

        foreach ($configurations as $configuration) {
            $service = $this->getMutexService($configuration);
            if (null === $service) {
                throw new \LogicException(sprintf(
                    'To use the @MutexRequest tag, you need to register a valid locker provider. %s is not registered',
                    $configuration->getService()
                ));
            }

            switch ($configuration->getMode()) {
                case MutexRequest::MODE_BLOCK:
                    $this->block($service, $configuration);
                    break;
                case MutexRequest::MODE_CHECK:
                    $this->check($service, $configuration);
                    break;
                default:
            }
        }
    }

    public function onKernelTerminate(PostResponseEvent $event)
    {
        // @TODO release locks here? __destroy methods works actually
    }

    /**
     * @param FilterControllerEvent $event
     * @return MutexRequest[]
     */
    private function loadConfiguration(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        $className  = class_exists('Doctrine\Common\Util\ClassUtils') ?
            ClassUtils::getClass($controller[0]) :
            get_class($controller[0])
        ;
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $classConfigurations  = $this->getConfigurations($this->reader->getClassAnnotations($object));
        $methodConfigurations = $this->getConfigurations($this->reader->getMethodAnnotations($method));

        $configurations = array_merge($classConfigurations, $methodConfigurations);

        return $configurations;
    }

    /**
     * @param array $annotations
     * @return MutexRequest[]
     */
    private function getConfigurations(array $annotations)
    {
        $configurations = [];
        foreach ($annotations as $configuration) {
            if ($configuration instanceof MutexRequest) {
                $configurations[] = $configuration;
            }
        }
        return $configurations;
    }

    /**
     * @param MutexRequest $configuration
     * @return LockerManagerInterface|null
     */
    private function getMutexService(MutexRequest $configuration)
    {
        try {
            return $this->container->get($configuration->getService());
        } catch (ServiceNotFoundException $e) {
        }
        return;
    }

    /**
     * Attempt to acquire the lock.
     *
     * @param LockerManagerInterface $service
     * @param MutexRequest           $configuration
     *
     * @throws ConflictHttpException
     */
    private function block(LockerManagerInterface $service, MutexRequest $configuration)
    {
        $this->check($service, $configuration);
        $service->acquireLock($configuration->getName(), null, $configuration->getTtl());
    }

    /**
     * Check if the lock is locked or not.
     *
     * @param LockerManagerInterface $service
     * @param MutexRequest           $configuration
     *
     * @throws ConflictHttpException
     */
    private function check(LockerManagerInterface $service, MutexRequest $configuration)
    {
        if ($service->isLocked($configuration->getName())) {
            $message = $configuration->getMessage();
            if (!$message) {
                $message = 'Resource is not available at this moment.';
            }
            throw new ConflictHttpException($message);
        }
    }
}
