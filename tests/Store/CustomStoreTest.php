<?php

namespace Tests\Store;

use IXarlie\MutexBundle\Store\CustomStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\StoreInterface;

class CustomStoreTest extends TestCase
{
    public function testSave()
    {
        $store = $this->createStore('save');

        $store->save(new Key(''));

        static::assertTrue(true);
    }

    public function testWaitAndSave()
    {
        $store = $this->createStore('waitAndSave');

        $store->waitAndSave(new Key(''));

        static::assertTrue(true);
    }

    public function testPutOffExpiration()
    {
        $store = $this->createStore('putOffExpiration');

        $store->putOffExpiration(new Key(''), 100);

        static::assertTrue(true);
    }

    public function testDelete()
    {
        $store = $this->createStore('delete');

        $store->delete(new Key(''));

        static::assertTrue(true);
    }

    /**
     * @inheritdoc
     */
    public function testExists()
    {
        $store = $this->createStore('exists');

        $store->exists(new Key(''));

        static::assertTrue(true);
    }

    /**
     * @param string $methodName
     *
     * @return CustomStore
     */
    private function createStore($methodName)
    {
        $store = $this->getMockBuilder(StoreInterface::class)->getMock();

        $store
            ->expects(static::once())
            ->method($methodName)
        ;

        return new CustomStore($store);
    }
}
