<?php

namespace IXarlie\MutexBundle\EventListener;

use HttpException;
use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Exception\MutexException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MutexExceptionListener.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexExceptionListener
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * MutexExceptionListener constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof MutexException) {
            return;
        }

        $configuration = $exception->getConfiguration();
        $exception     = new HttpException($configuration->getHttpCode(), $this->getTranslatedMessage($configuration));

        // Replace exception with a HttpException instance.
        $event->setException($exception);
    }

    /**
     * Get a translated configuration message.
     *
     * @param MutexRequest $configuration
     *
     * @return string
     */
    protected function getTranslatedMessage(MutexRequest $configuration)
    {
        if (null === $this->translator) {
            return $configuration->getMessage();
        }

        return $this->translator->trans($configuration->getMessage(), [], $configuration->getMessageDomain());
    }
}
