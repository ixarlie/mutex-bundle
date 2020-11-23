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
     * @var string
     */
    const MODE_BLOCK = 'block';

    /**
     * Check status of the mutex, in case is locked an exception is thrown. (do not attempt to acquire the mutex)
     * @var string
     */
    const MODE_CHECK = 'check';

    /**
     * Attempt to acquire the mutex, in case is locked, the request wait until the mutex is released.
     * @var string
     */
    const MODE_QUEUE = 'queue';

    /**
     * Release any locked mutex, then acquire it.
     */
    const MODE_FORCE = 'force';

    /**
     * Lock name. If you don't specify one the name will be a generated hash using request information.
     * @var string
     */
    protected $name;

    /**
     * One of the available modes.
     * @var string
     */
    protected $mode;

    /**
     * Some lockers implements a time-to-live option. This option is ignored for non compatible lockers.
     * @var int
     */
    protected $ttl;

    /**
     * Registered service to create the lock. Reduced or complete name can be used.
     * If you don't specify a value, the default locker will be used.
     * (redis.name == ixarlie_mutex.redis_factory.name)
     * @var string
     */
    protected $service;

    /**
     * HTTP options to change the behaviour of the http exceptions:
     *  - code: Http code status to throw is resource is locked.
     *  - message: Message of the exception.
     *  - domain: If want to use the translator using a domain
     * @var array
     */
    protected $http;

    /**
     * Append user information to the lock name to have isolated locks.
     * @var bool
     */
    protected $userIsolation = false;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
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
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @param string $service
     */
    public function setService($service)
    {
        $this->service = $service;
    }

    /**
     * @return boolean
     */
    public function isUserIsolation()
    {
        return $this->userIsolation;
    }

    /**
     * @param boolean $userIsolation
     */
    public function setUserIsolation($userIsolation)
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
    public function setHttp(array $http)
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
    public function isEmptyName()
    {
        return null === $this->name || '' === $this->name;
    }

    /**
     * @return bool
     */
    public function isEmptyService()
    {
        return null === $this->service || '' === $this->service;
    }
}
