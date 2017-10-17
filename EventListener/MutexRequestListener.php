<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
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
     * @var AnnotationReader
     */
    private $reader;

    /**
     * @var LockerManagerInterface[]
     */
    private $managers;

    /**
     * @var string
     */
    private $httpExceptionMessage;

    /**
     * @var int
     */
    private $httpExceptionCode;

    /**
     * @var int
     */
    private $maxQueueTimeout;

    /**
     * @var int
     */
    private $maxQueueTry;

    /**
     * It stores a relation between locker hash name and configuration
     * @var array
     */
    private $lockerMapping = [];

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
     * MutexRequestListener constructor.
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string                 $name
     * @param LockerManagerInterface $locker
     */
    public function addLockerManager($name, LockerManagerInterface $locker)
    {
        $this->managers[$name] = $locker;
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
     * @param string $message
     * @param int    $code
     */
    public function setHttpExceptionOptions($message, $code)
    {
        $this->httpExceptionCode    = $code;
        $this->httpExceptionMessage = $message;
    }

    /**
     * @param int $maxQueueTimeout
     */
    public function setMaxQueueTimeout($maxQueueTimeout)
    {
        $this->maxQueueTimeout = $maxQueueTimeout;
    }

    /**
     * @param int $maxQueueTry
     */
    public function setMaxQueueTry($maxQueueTry)
    {
        $this->maxQueueTry = $maxQueueTry;
    }

    /**
     * @param string $path          The request uri path
     * @param string $className     The controller name
     * @param string $methodName    The method name
     * @param string $userHash      The user hash
     *
     * @return string
     */
    public static function generateLockName($className, $methodName, $path, $userHash = '')
    {
        $name = sprintf(
            '%s_%s_%s_%s',
            preg_replace('|[\/\\\\]|', '_', $className),
            $methodName,
            str_replace('/', '_', $path),
            $userHash
        );
        // Use a hash in order that file lockers could work properly.
        return md5($name);
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
            
            $this->lockerMapping[$configuration->getName()] = sprintf(
                '%s->%s::%s@%s%s',
                $configuration->getService(),
                $className,
                $methodName,
                $request->getPathInfo(),
                $configuration->isUserIsolation() ? '+user' : ''
            );
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
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($methodName);

        $classConfigurations  = $this->getConfigurations($this->reader->getClassAnnotations($object), $className);
        $methodConfigurations = $this->getConfigurations($this->reader->getMethodAnnotations($method), $className, $methodName);

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
        if (!isset($this->managers[$id])) {
            throw new \RuntimeException(sprintf('Lock "%s" does not seem to exist.', $id));
        }

        return $this->managers[$id];
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
        $userHash = $configuration->isUserIsolation() ? $this->getIsolatedName() : '';
        $name     = $configuration->getName();
        if (null === $name || '' === $name) {
            $name     = self::generateLockName(
                $className,
                $methodName,
                $request->getPathInfo(),
                $userHash
            );
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
            $configuration->setMessage($this->httpExceptionMessage);
        }
        $httpCode = $configuration->getHttpCode();
        if (null === $httpCode || '' === $httpCode) {
            $configuration->setHttpCode($this->httpExceptionCode);
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
        $max   = $this->maxQueueTry ?: 3;
        $tries = 0;
        do {
            $result = $service->acquireLock($configuration->getName(), $this->getMaxTimeout(), $configuration->getTtl());
            $tries++;
        } while(false === $result && $tries < $max);
        
        // In case after the maximum tries, we cannot acquire the mutex, then throw a http exception
        if (false === $result) {
            throw new HttpException($configuration->getHttpCode(), $this->getTranslatedMessage($configuration));
        }
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

    /**
     * Do not set a timeout could produces an endless blocking between two requests.
     * We limit this time to the php max_execution_time.
     *
     * @return int
     */
    private function getMaxTimeout()
    {
        return ($this->maxQueueTimeout ?: intval(ini_get('max_execution_time'))) * 1000; // in ms
    }
}
