<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Exception\MutexConfigurationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
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
     * MutexDecoratorListener constructor.
     *
     * @param TokenStorageInterface|null $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage = null)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $request        = $event->getRequest();
        $configurations = $request->attributes->get('_ixarlie_mutex_request', []);

        /** @var MutexRequest $configuration */
        foreach ($configurations as $configuration) {
            $this->decorateService($configuration);
            $this->decorateName($request, $configuration);
        }
    }

    /**
     * @param Request      $request
     * @param MutexRequest $configuration
     */
    protected function decorateName(Request $request, MutexRequest $configuration): void
    {
        if ($configuration->isEmptyName()) {
            $name = $this->createDefaultName($request);
        } else {
            $name = $this->createCustomName($request, $configuration);
        }

        if ($configuration->isUserIsolation()) {
            $name = $name . '_' . $this->getIsolatedName();
        }

        // Use a hash in order flock stores can work properly.
        $configuration->setName('ixarlie_mutex_' . md5($name));
    }

    /**
     * @param MutexRequest $configuration
     */
    protected function decorateService(MutexRequest $configuration): void
    {
        $service = $configuration->getService();
        if ($configuration->isEmptyService()) {
            // Setting default factory
            $service = 'ixarlie_mutex.default_factory';
        } elseif (preg_match('/ixarlie_mutex\.\w+_factory\.\w+/', $service)) {
            // Service name matches the full qualified name for factories
        } elseif (preg_match('/(\w+)\.(\w+)/', $service, $matches)) {
            // Service matches type.name
            $service = sprintf('ixarlie_mutex.%s_factory.%s', $matches[1], $matches[2]);
        }

        $configuration->setService($service);
    }

    /**
     * Returns a unique hash for user.
     *
     * @return string
     * @throws MutexConfigurationException
     */
    private function getIsolatedName(): string
    {
        if (null !== $this->tokenStorage) {
            $token = $this->tokenStorage->getToken();

            return $token ? md5($token->serialize()) : '';
        }

        throw new MutexConfigurationException(
            '[MutexDecoratorListener] Cannot use isolation with missing "security.token_storage".'
        );
    }

    /**
     * Creates a default name
     * ControllerName_MethodName_PathInfo_[UserIsolation]
     *
     * @param Request $request
     *
     * @return string
     */
    private function createDefaultName(Request $request): string
    {
        return sprintf(
            '%s_%s',
            str_replace(['\\', ':'], ['_', '_'], $request->attributes->get('_controller')),
            str_replace('/', '_', $request->getPathInfo())
        );
    }

    /**
     * @param Request      $request
     * @param MutexRequest $configuration
     *
     * @return string
     */
    private function createCustomName(Request $request, MutexRequest $configuration): string
    {
        $name = $configuration->getName();
        preg_match_all('|\{([^\{\}]+)\}|', $name, $matches);
        $routeParams = $request->attributes->get('_route_params', []);

        foreach ($matches[1] as $i => $match) {
            if (!array_key_exists($match, $routeParams)) {
                throw new MutexConfigurationException(sprintf(
                    '[MutexDecoratorListener] Cannot find placeholder "%s" in request for name "%s"',
                    $match,
                    $configuration->getName()
                ));
            }

            $name = str_replace($matches[0][$i], $routeParams[$match], $name);
        }

        return $name;
    }
}
