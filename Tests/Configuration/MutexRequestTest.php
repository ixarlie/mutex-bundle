<?php

namespace IXarlie\MutexBundle\Tests\Configuration;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use PHPUnit\Framework\TestCase;

/**
 * Class MutexRequestTest.
 */
class MutexRequestTest extends TestCase
{
    public function testGetSetService()
    {
        $mutex = new MutexRequest(['service' => 'foo']);
        static::assertEquals('foo', $mutex->getService());

        $mutex->setService('bar');
        static::assertEquals('bar', $mutex->getService());
    }

    public function testGetSetMode()
    {
        $mutex = new MutexRequest(['mode' => 'foo']);
        static::assertEquals('foo', $mutex->getMode());

        $mutex->setMode('bar');
        static::assertEquals('bar', $mutex->getMode());
    }

    public function testGetSetMessageDomain()
    {
        $mutex = new MutexRequest(['messageDomain' => 'foo']);
        static::assertEquals('foo', $mutex->getMessageDomain());

        $mutex->setMessageDomain('bar');
        static::assertEquals('bar', $mutex->getMessageDomain());
    }

    public function testGetSetMessage()
    {
        $mutex = new MutexRequest(['message' => 'foo']);
        static::assertEquals('foo', $mutex->getMessage());

        $mutex->setMessage('bar');
        static::assertEquals('bar', $mutex->getMessage());
    }

    public function testGetSetTtl()
    {
        $mutex = new MutexRequest(['ttl' => 1000]);
        static::assertEquals(1000, $mutex->getTtl());

        $mutex->setTtl(2000);
        static::assertEquals(2000, $mutex->getTtl());
    }

    public function testGetSetHttpCode()
    {
        $mutex = new MutexRequest(['httpCode' => 400]);
        static::assertEquals(400, $mutex->getHttpCode());

        $mutex->setHttpCode(500);
        static::assertEquals(500, $mutex->getHttpCode());
    }

    public function testGetSetName()
    {
        $mutex = new MutexRequest(['name' => 'foo']);
        static::assertEquals('foo', $mutex->getName());

        $mutex->setName('bar');
        static::assertEquals('bar', $mutex->getName());
    }

    public function testGetSetIsUserIsolation()
    {
        $mutex = new MutexRequest(['userIsolation' => false]);
        static::assertEquals(false, $mutex->isUserIsolation());

        $mutex->setUserIsolation(true);
        static::assertEquals(true, $mutex->isUserIsolation());
    }
}
