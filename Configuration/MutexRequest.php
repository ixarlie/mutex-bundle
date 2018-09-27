<?php

namespace IXarlie\MutexBundle\Configuration;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * Class MutexLock
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class MutexRequest extends ConfigurationAnnotation
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
     * Lock name. If you don't specify one the name will be a generated hash using request information
     * @var string
     */
    protected $name;

    /**
     * One of the available modes.
     * @var string
     */
    protected $mode;

    /**
     * Some lockers implements a time-to-live option.
     * This option is ignored for non compatible lockers.
     * @var int
     */
    protected $ttl;

    /**
     * Registered service to create the lock. Reduced or complete name can be used.
     * If you don't specify a value, the default locker will be used.
     * (redis == i_xarlie_mutex.locker_redis)
     * @var string
     */
    protected $service;

    /**
     * HTTP Code to throw if resource is locked.
     * @var int
     */
    protected $httpCode;

    /**
     * Custom message for HTTP exception
     * @var string
     */
    protected $message;

    /**
     * Domain to translate the message
     * @var string
     */
    protected $messageDomain;

    /**
     * Append user information to the lock to have isolated locks
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
        if (null === $mode || '' === $mode) {
            throw new \InvalidArgumentException('Mode cannot be empty.');
        }

        if (!defined('\IXarlie\MutexBundle\Configuration\MutexRequest::MODE_' . strtoupper($mode))) {
            throw new \InvalidArgumentException("Mode $mode is not a valid mode.");
        }

        $this->mode = $mode;
    }

    /**
     * @return int
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * @param int $httpCode
     */
    public function setHttpCode($httpCode)
    {
        $this->httpCode = $httpCode;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
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
     * @return string
     */
    public function getMessageDomain()
    {
        return $this->messageDomain;
    }

    /**
     * @param string $messageDomain
     */
    public function setMessageDomain($messageDomain)
    {
        $this->messageDomain = $messageDomain;
    }
}
