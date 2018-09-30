<?php

namespace IXarlie\MutexBundle\EventListener;

use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Symfony\Component\Lock\LockInterface;

/**
 * Class MutexReleaseListener.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexReleaseListener
{
    /**
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $request = $event->getRequest();
        $locks   = $request->attributes->get('_ixarlie_mutex_locks', null);

        if (!is_array($locks)) {
            return;
        }

        /** @var LockInterface $lock */
        foreach ($locks as $lock) {
            $lock->release();
        }

        $request->attributes->remove('_ixarlie_mutex_locks');
    }
}
