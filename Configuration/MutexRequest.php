<?php

namespace IXarlie\MutexBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * Class MutexRequest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
final class MutexRequest extends ConfigurationAnnotation
{
    /**
     * Attempt to acquire the mutex, in case is locked an exception is thrown.
     *
     * @var string
     */
    const MODE_BLOCK = 'block';

    /**
     * Check status of the mutex, in case is locked an exception is thrown. (do not attempt to acquire the mutex)
     *
     * @var string
     */
    const MODE_CHECK = 'check';

    /**
     * Attempt to acquire the mutex, in case is locked, the request wait until the mutex is released.
     *
     * @var string
     */
    const MODE_QUEUE = 'queue';

    /**
     * Release any locked mutex, then acquire it.
     */
    const MODE_FORCE = 'force';

    /**
     * Lock name. If you don't specify one the name will be a generated hash using request information.
     *
     * @var string
     */
    protected $name;

    /**
     * One of the available modes.
     *
     * @var string
     */
    protected $mode;

    /**
     * Some lockers implements a time-to-live option. This option is ignored for non compatible lockers.
     *
     * @var int
     */
    protected $ttl;

    /**
     * Registered service to create the lock. Reduced or complete name can be used.
     * If you don't specify a value, the default locker will be used.
     * (redis.name == ixarlie_mutex.redis_factory.name)
     *
     * @var string
     */
    protected $service;

    /**
     * HTTP options to change the behaviour of the http exceptions:
     *  - code: Http code status to throw is resource is locked.
     *  - message: Message of the exception.
     *  - domain: If want to use the translator using a domain
     *
     * @var array
     */
    protected $http = [];

    /**
     * Append user information to the lock name to have isolated locks.
     *
     * @var bool
     */
    protected $userIsolation = false;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode(string $mode): void
    {
        switch ($mode) {
            case self::MODE_BLOCK:
            case self::MODE_CHECK:
            case self::MODE_FORCE:
            case self::MODE_QUEUE:
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Mode "%s" is not a valid mutex mode.', $mode));
        }

        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    /**
     * @param int|null $ttl
     */
    public function setTtl(?int $ttl): void
    {
        $this->ttl = $ttl;
    }

    /**
     * @return string
     */
    public function getService(): ?string
    {
        return $this->service;
    }

    /**
     * @param string|null $service
     */
    public function setService(?string $service): void
    {
        $this->service = $service;
    }

    /**
     * @return bool
     */
    public function isUserIsolation(): bool
    {
        return $this->userIsolation;
    }

    /**
     * @param bool $userIsolation
     */
    public function setUserIsolation(bool $userIsolation): void
    {
        $this->userIsolation = $userIsolation;
    }

    /**
     * @return array
     */
    public function getHttp(): array
    {
        return $this->http;
    }

    /**
     * @param array $http
     */
    public function setHttp(array $http): void
    {
        $this->http = $http;
    }

    /**
     * @inheritdoc
     */
    public function getAliasName()
    {
        return 'ixarlie_mutex_request';
    }

    /**
     * @inheritdoc
     */
    public function allowArray()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isEmptyName(): bool
    {
        return null === $this->name || '' === $this->name;
    }

    /**
     * @return bool
     */
    public function isEmptyService(): bool
    {
        return null === $this->service || '' === $this->service;
    }
}
