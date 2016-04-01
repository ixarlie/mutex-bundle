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
     * @var string
     */
    const MODE_BLOCK = 'block';
    /**
     * Just check if the mutex is released in order to be executed, but do not acquire it.
     * @var string
     */
    const MODE_CHECK = 'check';
    /**
     * Attempt to acquire the mutex, in case is locked, the request wait until the mutex is released.
     * @var string
     */
    const MODE_QUEUE = 'queue';
    /**
     * Release any locked mutex, then acquire it
     */
    const MODE_FORCE = 'force';

    /**
     * Lock name
     * @var string
     */
    protected $name;

    /**
     * One of the available modes.
     * @var string
     */
    protected $mode = self::MODE_BLOCK;

    /**
     * Some lockers implements a time-to-live option.
     * This option is ignored for non compatible lockers.
     * @var int
     */
    protected $ttl;

    /**
     * Registered service to create the lock. Reduced or complete name can be used.
     * (redis == i_xarlie_mutex.locker_redis)
     * @var string
     */
    protected $service;

    /**
     * HTTP Code to throw if resource is locked.
     * @var int
     */
    protected $httpCode = 409;

    /**
     * Custom message for HTTP exception
     * @var string
     */
    protected $message;

    /**
     * Append user information to the lock to have isolated locks
     * @var bool
     */
    protected $userIsolation = false;

    public function __construct(array $values)
    {
        foreach ($values as $k => $v) {
            if (!method_exists($this, $name = 'set'.$k)) {
                throw new \RuntimeException(sprintf('Unknown key "%s" for annotation "@%s".', $k, get_class($this)));
            }
            $this->$name($v);
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * return MutexRequest
     */
    public function setName($name)
    {
        if (empty($name)) {
            throw new \LogicException('@MutexRequest name is mandatory field');
        }
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
     *
     * return MutexRequest
     */
    public function setMode($mode)
    {
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
     *
     * return MutexRequest
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
     *
     * return MutexRequest
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
     *
     * return MutexRequest
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
     *
     * return MutexRequest
     */
    public function setService($service)
    {
        if (empty($service)) {
            throw new \LogicException('@MutexRequest service is mandatory field');
        }
        if (!preg_match('/^i_xarlie_mutex.locker_/', $service)) {
            $service = 'i_xarlie_mutex.locker_' . $service;
        }
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
     *
     * return MutexRequest
     */
    public function setUserIsolation($userIsolation)
    {
        $this->userIsolation = $userIsolation;
    }

}
