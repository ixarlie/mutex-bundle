<?php

namespace IXarlie\MutexBundle\Store;

use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\Key;
use Symfony\Component\Lock\PersistingStoreInterface;

/**
 * Class CustomStore.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class CustomStore implements BlockingStoreInterface
{
    /**
     * @var PersistingStoreInterface
     */
    private $store;

    /**
     * CustomStore constructor.
     *
     * @param PersistingStoreInterface $store
     */
    public function __construct(PersistingStoreInterface $store)
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

    /**
     * @inheritdoc
     */
    public function waitAndSave(Key $key)
    {
        if ($this->store instanceof BlockingStoreInterface) {
            $this->store->waitAndSave($key);
        }
    }
}
