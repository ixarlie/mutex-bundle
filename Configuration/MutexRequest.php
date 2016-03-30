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
    const MODE_BLOCK = 'block';
    const MODE_APPLY = 'apply';

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $mode = self::MODE_BLOCK;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var int
     */
    protected $ttl;

    /**
     * @var string
     */
    protected $service;

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
        $this->name = $name;
        return $this;
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
        return $this;
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
        return $this;
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
        return $this;
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
        $this->service = $service;
        return $this;
    }
}
