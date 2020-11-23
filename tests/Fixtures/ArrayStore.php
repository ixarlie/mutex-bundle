<?php

namespace Tests\Fixtures;

use Symfony\Component\Lock\BlockingStoreInterface;
use Symfony\Component\Lock\Key;

/**
 * Class ArrayStore.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
final class ArrayStore implements BlockingStoreInterface
{
    /**
     * @inheritdoc
     */
    public function save(Key $key)
    {
        $this->lock($key, false);
    }

    /**
     * @inheritdoc
     */
    public function waitAndSave(Key $key)
    {
        $this->lock($key, true);
    }

    /**
     * @inheritdoc
     */
    public function putOffExpiration(Key $key, $ttl)
    {
        // do nothing, the flock locks forever.
    }

    /**
     * @inheritdoc
     */
    public function delete(Key $key)
    {
        // The lock is maybe not acquired.
        if (!$key->hasState(__CLASS__)) {
            return;
        }

        $key->removeState(__CLASS__);
    }

    /**
     * @inheritdoc
     */
    public function exists(Key $key)
    {
        return $key->hasState(__CLASS__);
    }

    /**
     * @param Key  $key
     * @param bool $blocking
     */
    private function lock(Key $key, bool $blocking): void
    {
        // The lock is maybe already acquired.
        if ($key->hasState(__CLASS__)) {
            return;
        }

        $keyName = sprintf('sf.%s.lock', preg_replace('/[^a-z0-9\._-]+/i', '-', $key));

        $key->setState(__CLASS__, $keyName);
    }
}
