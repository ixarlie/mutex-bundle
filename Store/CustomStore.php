<?php

namespace IXarlie\MutexBundle\Store;

use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\Key;

/**
 * Class CustomStore.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class CustomStore implements BlockingStoreInterface
{
    /**
     * @var BlockingStoreInterface
     */
    private $store;

    /**
     * CustomStore constructor.
     *
     * @param BlockingStoreInterface $store
     */
    public function __construct(BlockingStoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * @inheritdoc
     */
    public function save(Key $key)
    {
        $this->store->save($key);
    }

    /**
     * @inheritdoc
     */
    public function waitAndSave(Key $key)
    {
        $this->store->waitAndSave($key);
    }

    /**
     * @inheritdoc
     */
    public function putOffExpiration(Key $key, $ttl)
    {
        $this->store->putOffExpiration($key, $ttl);
    }

    /**
     * @inheritdoc
     */
    public function delete(Key $key)
    {
        $this->store->delete($key);
    }

    /**
     * @inheritdoc
     */
    public function exists(Key $key)
    {
        return $this->store->exists($key);
    }
}
