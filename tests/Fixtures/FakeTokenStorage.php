<?php

namespace Tests\Fixtures;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class FakeTokenStorage.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class FakeTokenStorage implements TokenStorageInterface
{
    /**
     * @var TokenInterface
     */
    private $token;

    /**
     * @inheritdoc
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @inheritdoc
     */
    public function setToken(TokenInterface $token = null)
    {
        $this->token = $token;
    }
}
