<?php

namespace IXarlie\MutexBundle\Configuration;

/**
 * Class MutexLock
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class MutexRequest
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
     * Lock name. If you don't specify one the name will be a generated hash using request information
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
     * Some lockers implements a time-to-live option.
     * This option is ignored for non compatible lockers.
     *
     * @var int
     */
    protected $ttl;

    /**
     * Registered service to create the lock. Reduced or complete name can be used.
     * If you don't specify a value, the default locker will be used.
     * (redis == i_xarlie_mutex.locker_redis)
     *
     * @var string
     */
    protected $service;

    /**
     * HTTP Code to throw if resource is locked.
     *
     * @var int
     */
    protected $httpCode;

    /**
     * Custom message for HTTP exception
     *
     * @var string
     */
    protected $message;

    /**
     * Domain to translate the message
     *
     * @var string
     */
    protected $messageDomain;

    /**
     * Append user information to the lock to have isolated locks
     *
     * @var bool
     */
    protected $userIsolation = false;

    public function __construct(array $values)
    {
        foreach ($values as $k => $v) {
            if (!method_exists($this, $name = 'set' . $k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, get_class($this)));
            }
            $this->$name($v);
        }
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * return MutexRequest
     */
    public function setName(?string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getMode(): ?string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     *
     * return MutexRequest
     */
    public function setMode(?string $mode)
    {
        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    /**
     * @param int $httpCode
     *
     * return MutexRequest
     */
    public function setHttpCode(?int $httpCode)
    {
        $this->httpCode = $httpCode;
    }

    /**
     * @return string
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * return MutexRequest
     */
    public function setMessage(?string $message)
    {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getTtl(): ?int
    {
        return $this->ttl;
    }

    /**
     * @param int $ttl
     *
     * return MutexRequest
     */
    public function setTtl(?int $ttl)
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
     * @param string $service
     *
     * return MutexRequest
     */
    public function setService(?string $service)
    {
        $this->service = $service;
    }

    /**
     * @return boolean
     */
    public function isUserIsolation(): ?bool
    {
        return $this->userIsolation;
    }

    /**
     * @param boolean $userIsolation
     *
     * return MutexRequest
     */
    public function setUserIsolation(?bool $userIsolation)
    {
        $this->userIsolation = $userIsolation;
    }

    /**
     * @return string
     */
    public function getMessageDomain(): ?string
    {
        return $this->messageDomain;
    }

    /**
     * @param string $messageDomain
     */
    public function setMessageDomain(?string $messageDomain)
    {
        $this->messageDomain = $messageDomain;
    }
}
