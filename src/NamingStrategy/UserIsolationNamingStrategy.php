<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\NamingStrategy;

use IXarlie\MutexBundle\MutexRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserIsolationNamingStrategy.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 * @final
 */
class UserIsolationNamingStrategy implements NamingStrategy
{
    public function __construct(
        private readonly NamingStrategy $inner,
        private readonly ?TokenStorageInterface $tokenStorage = null
    ) {
    }

    /**
     * @throws \Exception
     */
    public function createName(MutexRequest $config, Request $request): string
    {
        $name = $this->inner->createName($config, $request);

        if (true === $config->userIsolation) {
            $name .= $this->getToken();
        }

        return $name;
    }

    /**
     * @throws \Exception
     */
    private function getToken(): string
    {
        if (null === $this->tokenStorage) {
            throw new \RuntimeException('Cannot use user isolation with missing "security.token_storage".');
        }

        $token = $this->tokenStorage->getToken();

        return $token ? md5(serialize($token)) : '';
    }
}
