<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\Common\Annotations\Reader as AnnotationsReader;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;

/**
 * Class MutexRequestListener
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexRequestListener
{
    /**
     * @var AnnotationsReader
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
     * @var bool
     */
    private $requestPlaceholder = false;

    /**
     * @var TranslatorInterface|LegacyTranslatorInterface
     */
    private $translator;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * MutexRequestListener constructor.
     *
     * @param AnnotationsReader $reader
     */
    public function __construct(AnnotationsReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param string                 $name
     * @param LockerManagerInterface $locker
     */
    public function addLockerManager(string $name, LockerManagerInterface $locker): void
    {
        $this->managers[$name] = $locker;
    }

    /**
     * @param bool $requestPlaceholder
     */
    public function setRequestPlaceholder(bool $requestPlaceholder): void
    {
        $this->requestPlaceholder = $requestPlaceholder;
    }

    /**
     * @param TranslatorInterface|LegacyTranslatorInterface $translator
     */
    public function setTranslator($translator): void
    {
        if (!$translator instanceof TranslatorInterface && !$translator instanceof LegacyTranslatorInterface) {
            throw new \InvalidArgumentException('Not a valid translator instance.');
        }

        $this->translator = $translator;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(?TokenStorageInterface $tokenStorage): void
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $message
     * @param int    $code
     */
    public function setHttpExceptionOptions(string $message, int $code): void
    {
        $this->httpExceptionCode    = $code;
        $this->httpExceptionMessage = $message;
    }

    /**
     * @param int $maxQueueTimeout
     */
    public function setMaxQueueTimeout(int $maxQueueTimeout): void
    {
        $this->maxQueueTimeout = $maxQueueTimeout;
    }

    /**
     * @param int $maxQueueTry
     */
    public function setMaxQueueTry(int $maxQueueTry): void
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
    public static function generateLockName(
        string $className,
        string $methodName,
        string $path,
        string $userHash = ''
    ): string {
        $name = sprintf(
            '%s_%s_%s_%s',
            preg_replace('|[\/\\\\]|', '_', $className),
            $methodName,
            str_replace('/', '_', $path),
            $userHash
        );

        // Use a hash in order that file lockers could work properly.
        return 'ix_mutex_' . md5($name);
    }

    /**
     * @param Request $request
     * @param string  $name
     *
     * @return string
     */
    public static function replacePlaceholders(Request $request, string $name): string
    {
        preg_match_all('|\{([^\{\}]+)\}|', $name, $matches);
        $routeParams = $request->attributes->get('_route_params', []);
        foreach ($matches[1] as $i => $match) {
            if (!array_key_exists($match, $routeParams)) {
                throw new \RuntimeException(sprintf('Cannot find placeholder %s in request', $match));
            }
            $name = str_replace($matches[0][$i], $routeParams[$match], $name);
        }

        return $name;
    }

    /**
     * @param ControllerEvent $event
     *
     * @throws HttpException
     */
    public function onKernelController(ControllerEvent $event): void
    {
        if (false === $event->isMasterRequest()) {
            return;
        }

        if (!is_array($controller = $event->getController())) {
            return;
        }

        $controller = $event->getController();
        $className  = class_exists('Doctrine\Common\Util\ClassUtils') ?
            \Doctrine\Common\Util\ClassUtils::getClass($controller[0]) :
            get_class($controller[0]);
        $methodName = $controller[1];

        $request        = $event->getRequest();
        $configurations = $this->getConfigurations($request, $className, $methodName);
        if (empty($configurations)) {
            return;
        }

        foreach ($configurations as $configuration) {

            if (method_exists($this, $configuration->getMode())) {
                $service = $this->getMutexService($configuration);
                call_user_func([$this, $configuration->getMode()], $service, $configuration);
            }
        }

        $request->attributes->set('mutex_requests', $configurations);
    }

    /**
     * @param TerminateEvent $event
     */
    public function onKernelTerminate(TerminateEvent $event): void
    {
        $this->releaseLocks($event->getRequest());
    }

    /**
     * @param Request $request
     */
    public function releaseLocks(Request $request): void
    {
        $configurations = $request->attributes->get('mutex_requests');
        if (!$configurations) {
            return;
        }

        /** @var MutexRequest $configuration */
        foreach ($configurations as $configuration) {
            $service = $this->getMutexService($configuration);
            $service->releaseLock($configuration->getName());
        }

        $request->attributes->remove('mutex_requests');
    }

    /**
     * @param Request $request
     * @param string  $className
     * @param string  $methodName
     *
     * @return MutexRequest[]
     */
    protected function getConfigurations(Request $request, string $className, string $methodName): array
    {
        $object      = new \ReflectionClass($className);
        $method      = $object->getMethod($methodName);
        $annotations = array_merge(
            $this->reader->getClassAnnotations($object),
            $this->reader->getMethodAnnotations($method)
        );

        $configurations = [];
        foreach ($annotations as $configuration) {
            if (!$configuration instanceof MutexRequest) {
                continue;
            }

            $mode = $configuration->getMode();
            if (null === $mode || '' === $mode) {
                throw new \RuntimeException("@MutexRequest mode option is required in $className ($methodName)");
            } elseif (!defined('\IXarlie\MutexBundle\Configuration\MutexRequest::MODE_' . strtoupper($mode))) {
                throw new \RuntimeException("@MutexRequest $mode is not a valid mode in $className ($methodName)");
            }

            $this->applyDefaults($configuration, $request, $className, $methodName);

            $configurations[] = $configuration;
        }

        return $configurations;
    }

    /**
     * @param MutexRequest $configuration
     *
     * @return LockerManagerInterface|null
     */
    private function getMutexService(MutexRequest $configuration): ?LockerManagerInterface
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
    protected function getTranslatedMessage(MutexRequest $configuration): ?string
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
    protected function getIsolatedName(): ?string
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
    protected function applyDefaults(
        MutexRequest $configuration,
        Request $request,
        string $className,
        string $methodName
    ): void {
        $userHash = $configuration->isUserIsolation() ? $this->getIsolatedName() : '';
        $name     = $configuration->getName();
        if (null === $name || '' === $name) {
            $name = self::generateLockName(
                $className,
                $methodName,
                $request->getPathInfo(),
                $userHash
            );
            $configuration->setName($name);
        } elseif ($this->requestPlaceholder) {
            $configuration->setName(self::replacePlaceholders($request, $name));
        }

        $service = $configuration->getService();
        if (null === $service || '' === $service) {
            $configuration->setService('i_xarlie_mutex.locker');
        } elseif (!preg_match('i_xarlie_mutex.locker_', $service)) {
            $configuration->setService('i_xarlie_mutex.locker_' . $service);
        }
        // Try to find the service to check its existence
        $this->getMutexService($configuration);

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
    private function block(LockerManagerInterface $service, MutexRequest $configuration): void
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
    private function check(LockerManagerInterface $service, MutexRequest $configuration): void
    {
        if (!$service->isLocked($configuration->getName())) {
            return;
        }
        throw new HttpException((int) $configuration->getHttpCode(), $this->getTranslatedMessage($configuration));
    }

    /**
     * @param LockerManagerInterface $service
     * @param MutexRequest           $configuration
     */
    private function queue(LockerManagerInterface $service, MutexRequest $configuration): void
    {
        $tries   = 0;
        $max     = $this->maxQueueTry ?: 3;
        $timeout = $this->maxQueueTimeout * 1000;
        do {
            $result = $service->acquireLock($configuration->getName(), $timeout, $configuration->getTtl());
            $tries++;
        } while (false === $result && $tries < $max);

        // In case after the maximum tries, we cannot acquire the mutex, then throw a http exception
        if (false === $result) {
            throw new HttpException((int) $configuration->getHttpCode(), $this->getTranslatedMessage($configuration));
        }
    }

    /**
     * @param LockerManagerInterface $service
     * @param MutexRequest           $configuration
     */
    private function force(LockerManagerInterface $service, MutexRequest $configuration): void
    {
        if ($service->isLocked($configuration->getName())) {
            $service->releaseLock($configuration->getName());
        }
        $service->acquireLock($configuration->getName(), null, $configuration->getTtl());
    }
}
