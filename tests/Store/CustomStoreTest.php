<?php

namespace Tests\Store;

use IXarlie\MutexBundle\Store\CustomStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\PersistingStoreInterface;

final class CustomStoreTest extends TestCase
{
    public function testSave(): void
    {
        $store = $this->createStore('save');

        $store->save(new Key(''));

        static::assertTrue(true);
    }

    public function testPutOffExpiration(): void
    {
        $store = $this->createStore('putOffExpiration');

        $store->putOffExpiration(new Key(''), 100);

        static::assertTrue(true);
    }

    public function testDelete(): void
    {
        $store = $this->createStore('delete');

        $store->delete(new Key(''));

        static::assertTrue(true);
    }

    public function testExists(): void
    {
        $store = $this->createStore('exists');

        $store->exists(new Key(''));

        static::assertTrue(true);
    }

    public function testWaitAndSaveForBlockingStores(): void
    {
        $store = $this->getMockBuilder(BlockingStoreInterface::class)->getMock();

        $store
            ->expects(static::once())
            ->method('waitAndSave')
        ;

        $store = new CustomStore($store);

        $store->waitAndSave(new Key(''));

        static::assertTrue(true);
    }

    /**
     * @param string $methodName
     *
     * @return CustomStore
     */
    private function createStore(string $methodName): CustomStore
    {
        $store = $this->getMockBuilder(PersistingStoreInterface::class)->getMock();

        $store
            ->expects(static::once())
            ->method($methodName)
        ;

        return new CustomStore($store);
    }
}
