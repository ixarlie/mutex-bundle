<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var LockerManagerInterface[]
     */
    private $lockers;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var TokenStorageInterface

     */
    private $tokenStorage;

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
     * @param string                 $name
     * @param LockerManagerInterface $locker
     */
    public function addLocker($name, LockerManagerInterface $locker)
    {
        $this->lockers[$name] = $locker;
    }

    /**
     * @param TranslatorInterface $translator
     */
    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param FilterControllerEvent $event
     *
     * @throws HttpException
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $controller = $event->getController();
        $className  = class_exists('Doctrine\Common\Util\ClassUtils') ?
            \Doctrine\Common\Util\ClassUtils::getClass($controller[0]) :
            get_class($controller[0])
        ;
        $methodName = $controller[1];

        $configurations = $this->loadConfiguration($className, $methodName);
        if (empty($configurations)) {
            return;
        }

        $attributes = [];
        $request    = $event->getRequest();
        foreach ($configurations as $configuration) {
            $this->applyDefaults($configuration, $request, $className, $methodName);

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
                    $attributes[] = $configuration;
                    break;
                case MutexRequest::MODE_CHECK:
                    $this->check($service, $configuration);
                    break;
                case MutexRequest::MODE_QUEUE:
                    $this->queue($service, $configuration);
                    $attributes[] = $configuration;
                    break;
                case MutexRequest::MODE_FORCE:
                    $this->force($service, $configuration);
                    $attributes[] = $configuration;
                    break;
                default:
                    break;
            }
        }

        $request->attributes->set('mutex_requests', $attributes);
    }

    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $this->releaseLocks($event->getRequest());
    }

    /**
     * @param Request $request
     */
    private function releaseLocks(Request $request)
    {
        $configurations = $request->attributes->get('mutex_requests');
        if (!$configurations) {
            return;
        }
        /** @var MutexRequest $configuration */
        foreach ($configurations as $configuration) {
            $service = $this->getMutexService($configuration);
            if (null === $service) {
                throw new \LogicException(sprintf(
                    'To use the @MutexRequest tag, you need to register a valid locker provider. %s is not registered',
                    $configuration->getService()
                ));
            }

            $service->releaseLock($configuration->getName());
        }

        $request->attributes->remove('mutex_requests');
    }

    /**
     * @param string $className
     * @param string $methodName
     *
     * @return MutexRequest[]
     */
    private function loadConfiguration($className, $methodName)
    {
        $reader = $this->getAnnotationReader();
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($methodName);

        $classConfigurations  = $this->getConfigurations($reader->getClassAnnotations($object), $className);
        $methodConfigurations = $this->getConfigurations($reader->getMethodAnnotations($method), $className, $methodName);

        $configurations = array_merge($classConfigurations, $methodConfigurations);

        return $configurations;
    }

    /**
     * @param array $annotations
     * @param string $className
     * @param string $methodName
     *
     * @return MutexRequest[]
     */
    private function getConfigurations(array $annotations, $className, $methodName = null)
    {
        $configurations = [];
        foreach ($annotations as $configuration) {
            if ($configuration instanceof MutexRequest) {

                $mode = $configuration->getMode();
                if (null === $mode || '' === $mode) {
                    $message = "@MutexRequest mode option is required in $className";
                    if ($methodName) {
                        $message = $message . "::$methodName";
                    }
                    throw new \LogicException($message);
                } elseif (!defined('\IXarlie\MutexBundle\Configuration\MutexRequest::MODE_' . strtoupper($mode))) {
                    $message = "@MutexRequest \"$mode\" is not a valid mode option in $className";
                    if ($methodName) {
                        $message = $message . "::$methodName";
                    }
                    throw new \LogicException($message);
                }

                $configurations[] = $configuration;
            }
        }
        return $configurations;
    }

    /**
     * @param MutexRequest $configuration
     *
     * @return LockerManagerInterface|null
     */
    private function getMutexService(MutexRequest $configuration)
    {
        $id = $configuration->getService();
        if (!isset($this->lockers[$id])) {
            throw new \RuntimeException(sprintf('Lock "%s" does not seem to exist.'));
        }

        return $this->lockers[$id];
    }

    /**
     * @return AnnotationReader
     */
    private function getAnnotationReader()
    {
        if (!$this->reader) {
            if (!$this->container->has('annotation_reader')) {
                throw new \LogicException(sprintf('Service annotation_reader is required for use @MutexRequest'));
            }
            $this->reader = $this->container->get('annotation_reader');
        }
        return $this->reader;
    }

    /**
     * Get a translated configuration message.
     *
     * @param MutexRequest $configuration
     *
     * @return string
     */
    private function getTranslatedMessage(MutexRequest $configuration)
    {
        if (null === $this->translator) {
            return $configuration->getMessage();
        }

        return $this->translator->trans($configuration->getMessage(), [], $configuration->getMessageDomain());
    }

    /**
     * Returns a unique hash for user.
     *
     * @return string
     */
    private function getIsolatedName()
    {
        if (null === $this->tokenStorage) {
            throw new \RuntimeException('You attempted to use user isolation. Did you forget configure user_isolation?');
        }
        if ($token = $this->tokenStorage->getToken()) {
            return md5($token->serialize());
        }

        return null;
    }

    /**
     * @param MutexRequest $configuration
     * @param Request      $request
     * @param string       $className
     * @param string       $methodName
     */
    private function applyDefaults(MutexRequest $configuration, Request $request, $className, $methodName)
    {
        $name = $configuration->getName();
        if (null === $name || '' === $name) {
            $name = sprintf(
                '%s_%s_%s',
                preg_replace('|[\/\\\\]|', '_', $className),
                $methodName,
                str_replace('/', '_', $request->getPathInfo())
            );
            $configuration->setName($name);
        }

        if ($configuration->isUserIsolation()) {
            $name = sprintf('%s_%s', $configuration->getName(), $this->getIsolatedName());
            $configuration->setName($name);
        }

        $service = $configuration->getService();
        if (null === $service || '' === $service) {
            $configuration->setService('i_xarlie_mutex.locker');
        } elseif (!preg_match('i_xarlie_mutex.locker_', $service)) {
            $configuration->setService('i_xarlie_mutex.locker_' . $service);
        }

        $message = $configuration->getMessage();
        if (null === $message || '' === $message) {
            $configuration->setMessage($this->container->getParameter('i_xarlie_mutex.http_exception.message'));
        }
        $httpCode = $configuration->getHttpCode();
        if (null === $httpCode || '' === $httpCode) {
            $configuration->setHttpCode($this->container->getParameter('i_xarlie_mutex.http_exception.code'));
        }
    }

    /**
     * Attempt to acquire the lock.
     *
     * @param LockerManagerInterface $service
     * @param MutexRequest           $configuration
     *
     * @throws HttpException
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
     * @throws HttpException
     */
    private function check(LockerManagerInterface $service, MutexRequest $configuration)
    {
        if (!$service->isLocked($configuration->getName())) {
            return;
        }
        throw new HttpException($configuration->getHttpCode(), $this->getTranslatedMessage($configuration));
    }

    /**
     * @param LockerManagerInterface $service
     * @param MutexRequest $configuration
     */
    private function queue(LockerManagerInterface $service, MutexRequest $configuration)
    {
        $service->acquireLock($configuration->getName(), null, $configuration->getTtl());
    }

    /**
     * @param LockerManagerInterface $service
     * @param MutexRequest $configuration
     */
    private function force(LockerManagerInterface $service, MutexRequest $configuration)
    {
        if ($service->isLocked($configuration->getName())) {
            $service->releaseLock($configuration->getName());
        }
        $service->acquireLock($configuration->getName(), null, $configuration->getTtl());
    }
}
