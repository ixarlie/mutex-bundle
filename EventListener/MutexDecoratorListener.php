<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class MutexDecoratorListener.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexDecoratorListener
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var array
     */
    private $decoratorOptions;

    /**
     * MutexDecoratorListener constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage = null)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param array $decoratorOptions
     */
    public function setDecoratorOptions(array $decoratorOptions = [])
    {
        $this->decoratorOptions = $decoratorOptions;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        $request          = $event->getRequest();
        $configurations   = $request->attributes->get('_ixarlie_mutex_request', []);

        /** @var MutexRequest $configuration */
        foreach ($configurations as $configuration) {
            $this->decorateService($request, $configuration);
            $this->decorateName($request, $configuration);
            $this->decorateOptions($request, $configuration);
        }
    }

    /**
     * @param Request      $request
     * @param MutexRequest $configuration
     */
    protected function decorateName(Request $request, MutexRequest $configuration)
    {
        $name = $configuration->getName();

        if (null === $name || '' === $name) {
            $userHash = $configuration->isUserIsolation() ? $this->getIsolatedName() : '';
            $name     = $this->generateLockName($request, $userHash);
        } elseif ($this->decoratorOptions['requestPlaceholder']) {
            $name = $this->replacePlaceholders($request, $name);
        }

        $configuration->setName($name);
    }

    /**
     * @param Request      $request
     * @param MutexRequest $configuration
     */
    protected function decorateService(Request $request, MutexRequest $configuration)
    {
        $service = $configuration->getService();

        if (null === $service || '' === $service) {
            $service = 'ixarlie_mutex.default_store';
        } elseif (preg_match('(\w+)\.(\w+)', $service, $matches)) {
            $service = sprintf('ixarlie_mutex.%s_store.%s', $matches[1], $matches[2]);
        }

        $configuration->setService($service);
    }

    /**
     * @param Request      $request
     * @param MutexRequest $configuration
     */
    protected function decorateOptions(Request $request, MutexRequest $configuration)
    {
        $options = [
            'httpExceptionMessage' => ['message', $configuration->getMessage()],
            'httpExceptionCode'    => ['httpCode', $configuration->getHttpCode()],
            'maxQueueTimeout'      => ['maxQueueTimeout', $configuration->getMaxQueueTimeout()],
            'maxQueueTry'          => ['maxQueueTry', $configuration->getMaxQueueTry()],
        ];

        $accessor = new PropertyAccessor();
        foreach ($options as $option => [$property, $value]) {
            if (null === $value || '' === $value) {
                $value = isset($this->decoratorOptions[$option]) ? $this->decoratorOptions[$option] : null;
                $accessor->setValue($configuration, $property, $value);
            }
        }
    }

    /**
     * Returns a unique hash for user.
     *
     * @return string
     */
    private function getIsolatedName()
    {
        if (null !== $this->tokenStorage) {
            $token = $this->tokenStorage->getToken();

            return $token ? md5($token->serialize()) : '';
        }

        throw new \RuntimeException('You attempted to use user isolation. Did you forget configure user_isolation?');
    }

    /**
     * @param Request $request
     * @param string  $userHash The user hash if any
     *
     * @return string
     */
    private function generateLockName(Request $request, $userHash = '')
    {
        list($className, $methodName) = explode(':', $request->attributes->get('_controller'));

        $name = sprintf(
            '%s_%s_%s_%s',
            preg_replace('|[\/\\\\]|', '_', $className),
            $methodName,
            str_replace('/', '_', $request->getPathInfo()),
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
    private function replacePlaceholders(Request $request, $name)
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
}
