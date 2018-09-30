<?php

namespace IXarlie\MutexBundle\EventListener;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Exception\MutexException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        $this->decorateHttp($configuration);

        $httpOptions   = $configuration->getHttp();
        $exception     = new HttpException($httpOptions['code'], $httpOptions['message']);

        // Replace exception with a HttpException instance.
        $event->setException($exception);
    }

    /**
     * @param MutexRequest $configuration
     */
    private function decorateHttp(MutexRequest $configuration)
    {
        $defaults = [
            'code'    => Response::HTTP_LOCKED,
            'message' => 'Resource is not available at this moment.',
            'domain'  => null
        ];

        $http = $configuration->getHttp();
        foreach ($defaults as $key => $value) {
            if (!isset($http[$key])) {
                $http[$key] = $value;
            }
        }

        if (null !== $http['domain']) {
            $http['message'] = $this->getTranslatedMessage($http['domain'], $http['message']);
        }

        $configuration->setHttp($http);
    }

    /**
     * Get a translated configuration message.
     *
     * @param string $domain
     * @param string $message
     *
     * @return string
     */
    protected function getTranslatedMessage($domain, $message)
    {
        if (null === $this->translator) {
            return $message;
        }

        return $this->translator->trans($message, [], $domain);
    }
}
