<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\MutexRequest;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Lock\LockInterface;

/**
 * Class TerminateListener.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class TerminateListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();
        $locks   = $request->attributes->get(MutexRequest::ATTRIBUTE, []);

        /** @var LockInterface $lock */
        foreach ($locks as $lock) {
            $lock->release();
        }

        $request->attributes->remove(MutexRequest::ATTRIBUTE);
    }
}
